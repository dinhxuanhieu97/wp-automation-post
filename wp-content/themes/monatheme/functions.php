<?php

/**
 * The template for displaying index.
 *
 * @package MONA.Media / Website
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// efine acf
// if (get_current_user_id() == 1) {
//     define('ACF_LITE', false);
// } else {
//     define('ACF_LITE', true);
// }

add_action('wp_enqueue_scripts', function() {
    $css_path = get_template_directory() . '/public/css/translate.css';
    $css_uri  = get_template_directory_uri() . '/public/css/translate.css';
    $version  = file_exists($css_path) ? filemtime($css_path) : null;
    wp_enqueue_style('monatheme-translate', $css_uri, array(), $version);
}, 20);

// Classic Editor
add_filter('use_block_editor_for_post', '__return_false');

add_filter( 'rank_math/sitemap/last_modified', function( $date, $object_type, $object_id ) {
return current_time( 'Y-m-d H:i:s' );
}, 10, 3 );

define('MONA_THEME_PATH', get_template_directory_uri());
define('MONA_SITE_URL', get_option('siteurl'));
define('MONA_SITE_TEMPLATE', MONA_SITE_URL . '/template');

define('APP_PATH', '/app');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('AJAX_PATH', APP_PATH . '/ajax');
define('HELPER_PATH', APP_PATH . '/helpers');
define('MODULE_PATH', APP_PATH . '/modules');

define('CORE_PATH', '/core');
define('FILES_PATH', '/partials');
define('ADMIN_PATH', CORE_PATH . '/admin');
define('ADMIN_INCLUDES_PATH', ADMIN_PATH . '/includes');
define('ADMIN_AJAX_PATH', ADMIN_PATH . '/ajax');

define('THEME_VERSION', '4.0.1');
define('MENU_FILTER_ADMIN', 'mona-filter-admin');
define('FILTER_ADMIN_SETTING', 'MonaSetting');

// define theme page
define('MONA_PAGE_HOME', get_option('page_on_front', true));
define('MONA_PAGE_BLOG', get_option('page_for_posts', true));
define('MONA_CUSTOM_LOGO', get_theme_mod('custom_logo'));

define('MONA_PAGE_ACCOUNT', url_to_postid(get_permalink(14)));
define('MONA_PAGE_LOGIN', url_to_postid(get_permalink(106)));
define('MONA_PAGE_REGISTER', url_to_postid(get_permalink(108)));
define('MONA_PAGE_FORGOT', url_to_postid(get_permalink(110)));
define('MONA_PAGE_PRIVACY', get_option('wp_page_for_privacy_policy'));
define('MONA_PAGE_CONTACT', url_to_postid(get_permalink(29)));
define('MONA_PAGE_EVENT', url_to_postid(get_permalink(31)));

require_once(get_template_directory() . '/__autoload.php');

// Add this function to calculate reading time
function custom_reading_time($content) {
    $word_count = str_word_count(strip_tags($content)); // Count words in the content
    $reading_speed = 200; // Average reading speed in words per minute

    $reading_time = ceil($word_count / $reading_speed); // Calculate reading time

    // Determine the postfix based on singular or plural
    $postfix = $reading_time > 1 ? __('phút đọc', 'monamedia') : __('phút đọc', 'monamedia');

    return $reading_time . ' ' . $postfix;
}

// Register the shortcode
function register_custom_reading_time_shortcode($atts) {
    global $post;
    $content = $post->post_content;
    return custom_reading_time($content);
}
add_shortcode('rt_reading_time', 'register_custom_reading_time_shortcode');

/**
 * Shortcode: [tra_cuu_tuoi_nghi_huu]
 * Tool tra cứu tuổi nghỉ hưu & thời điểm nghỉ hưu theo tháng/năm sinh + giới tính
 * (Điều kiện lao động bình thường) theo lộ trình NĐ 135/2020/NĐ-CP.
 */

add_shortcode('tra_cuu_tuoi_nghi_huu', function () {
    ob_start();

    // Unique ID để tránh xung đột nếu chèn nhiều lần trên 1 trang
    $uid = 'retire_tool_' . wp_generate_uuid4();
    ?>
    <style>
      .retire-tool{max-width:860px;border:1px solid #e5e7eb;border-radius:14px;padding:16px;background:#000; color:#fff}
      .retire-tool h3{margin:0 0 8px;font-size:20px}
      .retire-tool p{margin:0 0 14px;color:while;line-height:1.5}
      .retire-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:10px;align-items:end}
      .retire-field{grid-column:span 4}
      .retire-field label{display:block;font-weight:600;margin:0 0 6px}
      .retire-field input,.retire-field select{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:10px;outline:none;color: aliceblue;}
      .retire-actions{grid-column:span 12;display:flex;gap:10px;flex-wrap:wrap;margin-top:4px}
      .retire-btn{padding:10px 14px;border:0;border-radius:10px;cursor:pointer;background:#b2e08b;color:#fff;font-weight:600}
      .retire-btn.secondary{background:#f3f4f6;color:#b2e08b;border:1px solid #e5e7eb}
      .retire-result{margin-top:14px;padding:12px;border-radius:12px;border:1px solid #e5e7eb;line-height:1.55}
      .retire-badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;font-weight:700}
      .ok{background:#ecfdf5;border:1px solid #bbf7d0;color:#065f46}
      .warn{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412}
      .muted{color:white}
      .retire-note{margin-top:10px;color:white;font-size:13px}
      .retire-table{width:100%;border-collapse:collapse;margin-top:12px;font-size:14px}
      .retire-table th,.retire-table td{border:1px solid #e5e7eb;padding:8px 10px;text-align:left}
      .retire-table th{background:#b2e08b}
      @media (max-width:720px){
        .retire-field{grid-column:span 12}
      }
    </style>

    <div class="retire-tool" id="<?php echo esc_attr($uid); ?>">
      <h3>Tra cứu tuổi nghỉ hưu & thời điểm nghỉ hưu theo năm sinh</h3>
      <p>
        Nhập <b>tháng/năm sinh</b> và <b>giới tính</b> → tool tính <b>tuổi nghỉ hưu</b> (năm + tháng) và
        <b>thời điểm nghỉ hưu</b> (tháng/năm) theo lộ trình áp dụng cho điều kiện lao động bình thường.
      </p>

      <div class="retire-grid">
        <div class="retire-field">
          <label>Tháng sinh</label>
          <input data-rt="month" type="number" min="1" max="12" placeholder="Ví dụ: 6">
        </div>
        <div class="retire-field">
          <label>Năm sinh (dương lịch)</label>
          <input data-rt="year" type="number" min="1900" max="2100" placeholder="Ví dụ: 1969">
        </div>
        <div class="retire-field">
          <label>Giới tính</label>
          <select data-rt="gender" style="color: #6b7280;">
            <option value="female" style="color: #6b7280;">Nữ</option>
            <option value="male" style="color: #6b7280;">Nam</option>
          </select>
        </div>

        <div class="retire-actions">
          <button class="retire-btn" type="button" data-rt="calc">Tra cứu</button>
          <button class="retire-btn secondary" type="button" data-rt="reset">Nhập lại</button>
        </div>
      </div>

      <div class="retire-result" data-rt="result">
        <span class="muted">Kết quả sẽ hiển thị tại đây.</span>
      </div>

      <div class="retire-note">
        <b>Ghi chú:</b> Lộ trình tăng tuổi nghỉ hưu (điều kiện bình thường):
        <b>nam</b> tăng 3 tháng/năm đến 62 tuổi (từ 2028),
        <b>nữ</b> tăng 4 tháng/năm đến 60 tuổi (đến 2035).
      </div>

      <table class="retire-table" aria-label="Tóm tắt lộ trình 2025-2035">
        <thead>
          <tr>
            <th>Năm nghỉ hưu</th>
            <th>Nam</th>
            <th>Nữ</th>
          </tr>
        </thead>
        <tbody data-rt="tbody"></tbody>
      </table>
    </div>

    <script>
    (function(){
      const root = document.getElementById(<?php echo json_encode($uid); ?>);
      if(!root) return;

      const $month  = root.querySelector('[data-rt="month"]');
      const $year   = root.querySelector('[data-rt="year"]');
      const $gender = root.querySelector('[data-rt="gender"]');
      const $calc   = root.querySelector('[data-rt="calc"]');
      const $reset  = root.querySelector('[data-rt="reset"]');
      const $result = root.querySelector('[data-rt="result"]');
      const $tbody  = root.querySelector('[data-rt="tbody"]');

      // ====== Lộ trình theo NĐ 135/2020/NĐ-CP (điều kiện bình thường) ======
      function ageByYear(year, gender){
        if(gender === "male"){
          if(year <= 2021) return {y:60,m:3};
          if(year >= 2028) return {y:62,m:0};
          const diff = year - 2021; // 0..6
          const totalM = 60*12 + 3 + diff*3;
          return {y: Math.floor(totalM/12), m: totalM%12};
        }else{
          if(year <= 2021) return {y:55,m:4};
          if(year >= 2035) return {y:60,m:0};
          const diff = year - 2021; // 0..14
          const totalM = 55*12 + 4 + diff*4;
          return {y: Math.floor(totalM/12), m: totalM%12};
        }
      }

      function addYM(month, year, addYears, addMonths){
        let m = month + addMonths;
        let y = year + addYears;
        while(m > 12){ m -= 12; y += 1; }
        while(m <= 0){ m += 12; y -= 1; }
        return {month:m, year:y};
      }

      function findRetirement(month, year, gender){
        const start = Math.max(2021, year + 45);
        const end = year + 75;

        for(let retireYear = start; retireYear <= end; retireYear++){
          const a = ageByYear(retireYear, gender);
          const d = addYM(month, year, a.y, a.m);
          if(d.year === retireYear){
            return {retireYear, retireMonth: d.month, ageY:a.y, ageM:a.m};
          }
        }
        // fallback hiếm gặp
        const a = ageByYear(2035, gender);
        const d = addYM(month, year, a.y, a.m);
        return {retireYear: d.year, retireMonth: d.month, ageY:a.y, ageM:a.m};
      }

      function fmtAge(y,m){
        return m ? `${y} tuổi ${m} tháng` : `${y} tuổi`;
      }

      function renderSchedule(){
        const years = [];
        for(let y=2025;y<=2035;y++) years.push(y);

        $tbody.innerHTML = years.map(y=>{
          const m = ageByYear(y,"male");
          const f = ageByYear(y,"female");
          const mTxt = m.m ? `${m.y} tuổi ${m.m} tháng` : `${m.y} tuổi`;
          const fTxt = f.m ? `${f.y} tuổi ${f.m} tháng` : `${f.y} tuổi`;
          return `<tr><td><b>${y}</b></td><td>${mTxt}</td><td>${fTxt}</td></tr>`;
        }).join("");
      }

      function showError(msg){
        $result.innerHTML = `<span class="retire-badge warn">Thiếu dữ liệu</span>
          <div style="margin-top:8px">${msg}</div>`;
      }

      function calc(){
        const m = parseInt($month.value, 10);
        const y = parseInt($year.value, 10);
        const g = $gender.value;

        if(!m || m < 1 || m > 12 || !y || y < 1900 || y > 2100){
          showError("<b>Vui lòng nhập đúng:</b> tháng 1–12 và năm sinh hợp lệ (ví dụ: 06/1969).");
          return;
        }

        const r = findRetirement(m, y, g);
        const genderTxt = (g === "male") ? "Nam" : "Nữ";
        $result.innerHTML = `
          <span class="retire-badge ok">Kết quả</span>
          <div style="margin-top:8px">
            <div><b>Thông tin:</b> ${genderTxt}, sinh <b>${String(m).padStart(2,"0")}/${y}</b></div>
            <div style="margin-top:6px"><b>Tuổi nghỉ hưu (theo lộ trình):</b> <b>${fmtAge(r.ageY, r.ageM)}</b></div>
            <div style="margin-top:6px"><b>Thời điểm nghỉ hưu dự kiến:</b> <b>${String(r.retireMonth).padStart(2,"0")}/${r.retireYear}</b></div>
            <div class="muted" style="margin-top:8px">
              (Áp dụng cho điều kiện lao động bình thường; trường hợp nghỉ hưu sớm/tuổi cao hơn có điều kiện riêng.)
            </div>
          </div>
        `;
      }

      function reset(){
        $month.value = "";
        $year.value = "";
        $gender.value = "female";
        $result.innerHTML = `<span class="muted">Kết quả sẽ hiển thị tại đây.</span>`;
      }

      $calc.addEventListener("click", calc);
      $reset.addEventListener("click", reset);
      $year.addEventListener("keydown", (e)=>{ if(e.key === "Enter") calc(); });
      $month.addEventListener("keydown", (e)=>{ if(e.key === "Enter") calc(); });

      renderSchedule();
    })();
    </script>
    <?php

    return ob_get_clean();
});
