export default function LoadMoreProductModule() {
  const speed = 800;
  const hash = window.location.hash;
  if ($(hash).length) scrollToID(hash, speed);
  function scrollToID(id, speed, number = 200) {
    const offSet = $("header").outerHeight();
    const section = $(id).offset();
    const targetOffset = section.top - offSet - number;
    $("html,body").animate({ scrollTop: targetOffset }, speed);
  }
  var progress = null;
  function makeAjaxRequest(paged, formdata) {
    return $.ajax({
      type: "POST",
      url: mona_ajax_url.ajaxURL,
      data: {
        action: "mona_ajax_load_more",
        formdata: formdata,
        paged: paged,
      },
    });
  }
  $(document).on(
    "click",
    ".pagination-posts-ajax a.page-numbers",
    function (e) {
      e.preventDefault();
      var $this = $(this);
      var form = $this.closest("form");
      var processing = form;
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
      var formdata = form.serialize();
      if (progress) {
        progress.abort();
      }
      progress = $.Deferred();
      makeAjaxRequest(paged, formdata)
        .done(function (result) {
          $(".post-list").html(result.data.html);
          $(".pagination-posts-ajax").html(result.data.btn);
          processing.removeClass("loading");
          scrollToID(".post-list", 500);

          progress.resolve(result);
        })
        .fail(function (request) {
          processing.removeClass("loading");
          progress.reject(request);
        })
        .always(function () {
          progress = null;
        });
      processing.addClass("loading");
    }
  );
  $(document).ready(function () {
    $(".formLoadAjax").keydown(function (event) {
      if (event.keyCode == 13) {
        event.preventDefault();
        return false;
      }
    });
  });
  $(document).on("submit", ".formLoadAjax", function (e) {
    e.preventDefault();
    var $this = $(this);
    var formdata = $this.serialize();
    var processing = $this;
    if (progress) {
      progress.abort();
    }
    progress = $.Deferred();
    makeAjaxRequest(1, formdata)
      .done(function (result) {
        $(".post-list").html(result.data.html);
        $(".pagination-posts-ajax").html(result.data.btn);
        processing.removeClass("loading");
        progress.resolve(result);
      })
      .fail(function (request) {
        processing.removeClass("loading");
        progress.reject(request);
      })
      .always(function () {
        progress = null;
      });
    processing.addClass("loading");
  });
  $(document).on("change", ".onChangePostAjax", function (e) {
    var $this = $(this).closest("form");
    var formdata = $this.serialize();
    var processing = $this;
    if (progress) {
      progress.abort();
    }
    progress = $.Deferred();
    makeAjaxRequest(1, formdata)
      .done(function (result) {
        $(".post-list").html(result.data.html);
        $(".pagination-posts-ajax").html(result.data.btn);
        processing.removeClass("loading");
        progress.resolve(result);
      })
      .fail(function (request) {
        processing.removeClass("loading");
        progress.reject(request);
      })
      .always(function () {
        progress = null;
      });
    processing.addClass("loading");
  });
  function makeAjaxRequest(paged, formdata) {
    return $.ajax({
      type: "POST",
      url: mona_ajax_url.ajaxURL,
      data: {
        action: "mona_ajax_load_more",
        formdata: formdata,
        paged: paged,
      },
    });
  }

  $(document).on("click", "#monaLoadMore", function (e) {
    e.preventDefault();
    var $this = $(this);
    var processing = $this.closest(".mona-load-btn");
    var paged = $this.data("paged");
    var formdata = $this.closest("form").serialize();

    if (progress) {
      progress.abort();
    }

    progress = $.Deferred();

    makeAjaxRequest(paged, formdata)
      .done(function (result) {
        $(".home_more--list").append(result.data.html);
        $(".mona-load-btn").html(result.data.btn);
        processing.removeClass("loading");
        progress.resolve(result);
      })
      .fail(function (request) {
        processing.removeClass("loading");
        progress.reject(request);
      })
      .always(function () {
        progress = null;
      });

    processing.addClass("loading");
  });

  $(document).on("click", ".tab-click", function (e) {
    e.preventDefault();
    var $this = $(this);
    var parent = $this.parents(".list-post");
    parent.find(".tab-click").removeClass("actived");
    $this.addClass("actived");
    var processing = parent.find(".mona-list-posts");
    var cat = $this.data("cat");
    var layout = $this.data("layout");
    if (progress) {
      progress.abort();
    }
    progress = $.ajax({
      type: "POST",
      url: mona_ajax_url.ajaxURL,
      data: {
        action: "mona_ajax_tab_load",
        cat: cat,
        layout: layout,
      },
      beforeSend: function () {
        processing.addClass("loading");
      },
    })
      .done(function (result) {
        processing.html(result.data.html);
      })
      .fail(function () {
        // Handle error
      })
      .always(function () {
        processing.removeClass("loading");
      });
  });
  $(document).ready(function () {
    $(".searchform").on("submit", function (e) {
      if ($(this).find(".search-field").val() == "") {
        e.preventDefault();
      }
    });
  });
}
