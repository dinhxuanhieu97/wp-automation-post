"""AI service để rewrite bài viết theo chuẩn SEO."""
import json
import time
from typing import Dict, Optional, List
from openai import OpenAI
import google.generativeai as genai
from google.api_core.exceptions import ResourceExhausted
from app.config import get_settings

settings = get_settings()

# Configure Gemini
genai.configure(api_key=settings.GEMINI_API_KEY)

# Configure Groq (OpenAI-compatible API)
groq_client = OpenAI(
    api_key=settings.GROQ_API_KEY,
    base_url="https://api.groq.com/openai/v1"
) if settings.GROQ_API_KEY else None

# Configure DeepSeek (OpenAI-compatible API) - support multiple keys
def get_deepseek_clients():
    """Create list of DeepSeek clients from comma-separated keys."""
    if not settings.DEEPSEEK_API_KEY:
        return []
    keys = [k.strip() for k in settings.DEEPSEEK_API_KEY.split(",") if k.strip()]
    return [OpenAI(api_key=k, base_url="https://api.deepseek.com/v1") for k in keys]

deepseek_clients = get_deepseek_clients()


def fix_json_response(text: str) -> str:
    """Fix common JSON formatting issues from AI responses."""
    import re
    text = text.strip()
    # Remove markdown code blocks if present
    text = re.sub(r'^```json\s*', '', text)
    text = re.sub(r'^```\s*', '', text)
    text = re.sub(r'```$', '', text)
    # Fix common issues: trailing commas, missing quotes
    text = re.sub(r',\s*}', '}', text)
    text = re.sub(r',\s*]', ']', text)
    # Remove control characters
    text = re.sub(r'[\x00-\x1f\x7f-\x9f]', '', text)
    # Fix unescaped newlines in strings
    text = re.sub(r'(?<!\\)"([^"\\]*(?:\\.[^"\\]*)*)\n([^"\\]*(?:\\.[^"\\]*)*)"', r'"\1\\n\2"', text)
    return text


def retry_with_delay(func, max_retries=3, delay=60):
    """Retry function with delay for rate limiting."""
    for attempt in range(max_retries):
        try:
            return func()
        except ResourceExhausted as e:
            if attempt < max_retries - 1:
                time.sleep(delay)
                continue
            raise
    return None


class AIRewriter:
    def __init__(self):
        # Use gemini-2.5-flash (available model from API)
        self.model = genai.GenerativeModel('gemini-2.5-flash')
        self.groq = groq_client
        self.deepseek_clients = deepseek_clients
        self.deepseek_index = 0  # Rotate through keys
        
    def _rewrite_with_deepseek(self, prompt: str) -> str:
        """Use DeepSeek API as fallback with key rotation."""
        if not self.deepseek_clients:
            raise ValueError("DeepSeek API not configured")
        
        # Try each key until one works
        errors = []
        for i in range(len(self.deepseek_clients)):
            client = self.deepseek_clients[self.deepseek_index]
            try:
                response = client.chat.completions.create(
                    model="deepseek-chat",
                    messages=[
                        {"role": "system", "content": "Bạn là content writer SEO chuyên nghiệp. Trả về JSON hợp lệ."},
                        {"role": "user", "content": prompt}
                    ],
                    temperature=0.7
                )
                return response.choices[0].message.content
            except Exception as e:
                errors.append(f"Key {self.deepseek_index}: {str(e)}")
                self.deepseek_index = (self.deepseek_index + 1) % len(self.deepseek_clients)
        
        raise Exception(f"All DeepSeek keys failed: {errors}")
        
    def _rewrite_with_groq(self, prompt: str) -> str:
        """Use Groq API as fallback."""
        if not self.groq:
            raise ValueError("Groq API not configured")
        
        response = self.groq.chat.completions.create(
            model="llama-3.1-8b-instant",  # Fast and cheap model
            messages=[
                {"role": "system", "content": "Bạn là content writer SEO chuyên nghiệp. Trả về JSON hợp lệ."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.7,
            response_format={"type": "json_object"}
        )
        return response.choices[0].message.content
    
    def rewrite_article(
        self, 
        title: str, 
        content: str,
        tone: str = "professional",
        target_length: int = 1500
    ) -> Dict[str, str]:
        """
        Rewrite bài viết với giá trị bổ sung, không copy nguyên văn.
        
        Returns:
            Dict với rewritten_title, rewritten_content
        """
        prompt = f"""
Bạn là một chuyên gia content writer SEO. Nhiệm vụ của bạn là VIẾT LẠI bài viết dưới đây theo cách:

1. KHÔNG copy nguyên văn - viết hoàn toàn mới
2. KHÔNG spin máy móc - tạo nội dung có giá trị thực sự
3. Thêm giá trị bổ sung: ví dụ thực tế, giải thích chi tiết, góc nhìn mới
4. Viết theo cấu trúc: Introduction → Main Points (có heading) → Practical Examples → Conclusion
5. Tone: {tone}
6. Target length: ~{target_length} words

TIÊU ĐỀ GỐC: {title}

NỘI DUNG GỐC:
{content[:3000]}...

Trả về JSON format:
{{
    "rewritten_title": "Tiêu đề mới hấp dẫn, có keyword",
    "rewritten_content": "Nội dung đã viết lại, định dạng markdown",
    "key_points": ["điểm chính 1", "điểm chính 2"],
    "suggested_keywords": ["keyword 1", "keyword 2", "keyword 3"]
}}
"""
        
        def _generate():
            generation_config = {
                "temperature": 0.7,
                "response_mime_type": "application/json"
            }
            return self.model.generate_content(prompt, generation_config=generation_config)
        
        try:
            response = retry_with_delay(_generate, max_retries=2, delay=60)
            fixed_text = fix_json_response(response.text)
            result = json.loads(fixed_text)
            return {"success": True, **result}
        except json.JSONDecodeError as json_err:
            try:
                fixed_text = fix_json_response(response.text)
                result = json.loads(fixed_text)
                return {"success": True, **result}
            except:
                pass
            # Try Groq first, then DeepSeek
            if self.groq:
                try:
                    content = self._rewrite_with_groq(prompt)
                    result = json.loads(content)
                    return {"success": True, **result}
                except Exception as groq_error:
                    pass
            if self.deepseek_clients:
                try:
                    content = self._rewrite_with_deepseek(prompt)
                    result = json.loads(content)
                    return {"success": True, **result}
                except Exception as deepseek_error:
                    return {"success": False, "error": f"JSON error: {str(json_err)}. Groq failed. DeepSeek failed: {str(deepseek_error)}"}
            return {"success": False, "error": f"JSON parse error: {str(json_err)}"}
        except Exception as e:
            if self.groq:
                try:
                    content = self._rewrite_with_groq(prompt)
                    result = json.loads(content)
                    return {"success": True, **result}
                except Exception as groq_error:
                    pass
            if self.deepseek_clients:
                try:
                    content = self._rewrite_with_deepseek(prompt)
                    result = json.loads(content)
                    return {"success": True, **result}
                except Exception as deepseek_error:
                    return {"success": False, "error": f"Gemini failed: {str(e)}. Groq failed. DeepSeek failed: {str(deepseek_error)}"}
            return {"success": False, "error": str(e)}
    
    def generate_seo_metadata(
        self, 
        title: str, 
        content: str,
        keywords: list
    ) -> Dict[str, str]:
        """Generate SEO metadata: slug, meta title, meta description, FAQ."""
        
        prompt = f"""
Tạo SEO metadata cho bài viết:

TIÊU ĐỀ: {title}
KEYWORDS: {', '.join(keywords)}

Trả về JSON:
{{
    "slug": "url-slug-seo-friendly",
    "meta_title": "Meta title (max 60 chars, có keyword)",
    "meta_description": "Meta description (max 160 chars, compelling)",
    "faq": [
        {{"question": "Câu hỏi 1?", "answer": "Câu trả lời chi tiết"}},
        {{"question": "Câu hỏi 2?", "answer": "Câu trả lời chi tiết"}}
    ]
}}
"""
        
        def _generate():
            generation_config = {
                "temperature": 0.7,
                "response_mime_type": "application/json"
            }
            return self.model.generate_content(prompt, generation_config=generation_config)
        
        try:
            response = retry_with_delay(_generate, max_retries=2, delay=60)
            fixed_text = fix_json_response(response.text)
            result = json.loads(fixed_text)
            return {"success": True, **result}
        except json.JSONDecodeError as json_err:
            try:
                fixed_text = fix_json_response(response.text)
                result = json.loads(fixed_text)
                return {"success": True, **result}
            except:
                pass
            if self.groq:
                try:
                    response = self.groq.chat.completions.create(
                        model="llama-3.1-8b-instant",
                        messages=[
                            {"role": "system", "content": "Bạn là SEO expert. Trả về JSON."},
                            {"role": "user", "content": prompt}
                        ],
                        temperature=0.7,
                        response_format={"type": "json_object"}
                    )
                    result = json.loads(response.choices[0].message.content)
                    return {"success": True, **result}
                except Exception as groq_error:
                    pass
            if self.deepseek_clients:
                try:
                    content = self._rewrite_with_deepseek(prompt)
                    result = json.loads(content)
                    return {"success": True, **result}
                except Exception as deepseek_error:
                    return {"success": False, "error": f"JSON error: {str(json_err)}. Groq failed. DeepSeek failed: {str(deepseek_error)}"}
            return {"success": False, "error": f"JSON parse error: {str(json_err)}"}
        except Exception as e:
            if self.groq:
                try:
                    response = self.groq.chat.completions.create(
                        model="llama-3.1-8b-instant",
                        messages=[
                            {"role": "system", "content": "Bạn là SEO expert. Trả về JSON."},
                            {"role": "user", "content": prompt}
                        ],
                        temperature=0.7,
                        response_format={"type": "json_object"}
                    )
                    result = json.loads(response.choices[0].message.content)
                    return {"success": True, **result}
                except Exception as groq_error:
                    pass
            if self.deepseek_clients:
                try:
                    content = self._rewrite_with_deepseek(prompt)
                    result = json.loads(content)
                    return {"success": True, **result}
                except Exception as deepseek_error:
                    return {"success": False, "error": f"Gemini failed: {str(e)}. Groq failed. DeepSeek failed: {str(deepseek_error)}"}
            return {"success": False, "error": str(e)}
    
    def generate_image_prompt(self, title: str, content_summary: str) -> str:
        """Generate prompt cho thumbnail image."""
        
        prompt = f"""
Tạo prompt để tạo ảnh thumbnail 1240x1240 cho bài viết:

TIÊU ĐỀ: {title}
TÓM TẮT: {content_summary[:500]}

Yêu cầu:
- Phong cách: professional, modern, suitable for blog thumbnail
- Kích thước: square 1:1 ratio
- Nội dung: visually appealing, related to article topic
- Không có text trong ảnh

Trả về chỉ prompt text, không có giải thích thêm.
"""
        
        def _generate():
            generation_config = {
                "temperature": 0.8,
                "max_output_tokens": 200
            }
            return self.model.generate_content(prompt, generation_config=generation_config)
        
        try:
            response = retry_with_delay(_generate, max_retries=3, delay=60)
            return response.text.strip()
        except Exception as e:
            return f"Professional blog thumbnail illustration about {title}, modern style, 1:1 ratio, no text"
