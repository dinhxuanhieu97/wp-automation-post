export default function LoadMorePostModule() {
  const speed = 800;
  const hash = window.location.hash;
  if ($(hash).length) scrollToID(hash, speed);
  $(".btn-scroll").on("click", function (e) {
    e.preventDefault();
    const href = $(this).find("> a").attr("href") || $(this).attr("href");
    const id = href.slice(href.lastIndexOf("#"));
    if ($(id).length) {
      scrollToID(id, speed);
    } else {
      window.location.href = href;
    }
  });
  function scrollToID(id, speed, number) {
    const offSet = $("header").outerHeight();
    const section = $(id).offset();
    const targetOffset = section.top - offSet - number;
    $("html,body").animate({ scrollTop: targetOffset }, speed);
  }

  // const mainElement = document.querySelector("main");

  /** Danh sách bài viết AJAX , danh sách bài viết có loadmore **/
  function getPostListPagedBlog(
    flag,
    $this,
    formdata,
    processing,
    action,
    paged = 1
  ) {
    if (!processing.hasClass("loading")) {
      $.ajax({
        url: mona_ajax_url.ajaxURL,
        type: "post",
        data: {
          action: "mona_ajax_loadmore_post",
          formdata: formdata,
          paged: paged,
          action_layout: action,
          action_flag: flag,
        },
        error: function (request) {
          processing.removeClass("loading");
        },
        beforeSend: function (response) {
          processing.addClass("loading");
        },
        success: function (result) {
          if (result.success) {
            console.log("success");
            if (
              result.data.action_return == "reload" &&
              result.data.posts_html != ""
            ) {
              $this.find("#blogLoadMoreJS").html(result.data.posts_html);
              scrollToID("." + result.data.scroll, 500, 200);
            } else if (
              result.data.action_return == "loadmore" &&
              result.data.posts_html != ""
            ) {
              $this.find(".blogLoadMoreJS").remove();
              $this.find("#blogLoadMoreJS").append(result.data.posts_html);
            }
          }
          processing.removeClass("loading");
        },
      });
    }
  }

  $(document).ready(function () {
    $("#formPostAjax").keydown(function (event) {
      if (event.keyCode == 13) {
        event.preventDefault();
        return false;
      }
    });
  });
  $(document).on("submit", "#formPostAjax", function (e) {
    var $this = $(this);
    var formdata = $this.serialize();
    var processing = $this.find("#blogLoadMoreJS");
    var layout = $this.data("layout");
    getPostListPagedBlog(true, $this, formdata, processing, layout);
  });

  $(document).on("change", ".onChangePostAjax", function (e) {
    var $this = $(this).closest("form");
    var layout = $this.data("layout");
    var formdata = $(this).closest("form").serialize();
    var processing = $(this).closest("form").find("#monaPostsList");
    getPostListPagedBlog(false, $this, formdata, processing, layout);
  });
  $(document).on("click", ".monaClickPostAjax", function (e) {
    var $this = $(this).closest("form");
    var layout = $this.data("layout");
    var formdata = $(this).closest("form").serialize();
    var processing = $(this).closest("form").find("#monaPostsList");
    getPostListPagedBlog(false, $this, formdata, processing, layout);
  });
  $(document).on(
    "click",
    ".pagination-posts-ajax a.page-numbers",
    function (e) {
      e.preventDefault();
      var $this = $(this);
      var form = $this.closest("form");
      var pagination = $this.closest(".pagination-posts-ajax");
      var pagedText = $this.text();
      var paged = pagedText.match(/\d+/);
      if (!paged) {
        if (!$this.hasClass("next")) {
          var pagedCurrentText = parseInt(
            pagination.find(".page-numbers.current").text()
          );
          var paged = pagedCurrentText - 1;
        } else {
          var pagedCurrentText = parseInt(
            pagination.find(".page-numbers.current").text()
          );
          var paged = pagedCurrentText + 1;
        }
      } else {
        paged = paged[0];
      }
      var formdata = $this.closest("form").serialize();
      var processing = $this.closest(".monaPostsList");
      getPostListPagedBlog(true, form, formdata, processing, "reload", paged);
    }
  );
  $(document).on("click", ".blogLoadMoreJS", function (e) {
    e.preventDefault();
    var $this = $(this);
    var paged = $this.data("paged");
    var form = $this.closest("form");
    var formdata = $this.closest("form").serialize();
    getPostListPagedBlog(true, form, formdata, $this, "loadmore", paged);
  });
  $(document).on("click", ".blogLoadMoreJS", function (e) {
    e.preventDefault();
    var $this = $(this);
    var paged = $this.data("paged");
    var form = $this.closest("form");
    var formdata = $this.closest("form").serialize();
    getPostListPagedBlog(true, form, formdata, $this, "loadmore", paged);
  });
}
