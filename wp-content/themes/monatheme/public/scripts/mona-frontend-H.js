
  // const speed = 800;
  // // console.log("aaskfasgvjnas");
  // const hash = window.location.hash;
  // if ($(hash).length) scrollToID(hash, speed);
  // $(".btn-scroll").on("click", function (e) {
  //   e.preventDefault();
  //   const href = $(this).find("> a").attr("href") || $(this).attr("href");
  //   const id = href.slice(href.lastIndexOf("#"));
  //   if ($(id).length) {
  //     scrollToID(id, speed);
  //   } else {
  //     window.location.href = href;
  //   }
  // });
  // function scrollToID(id, speed, number) {
  //   const offSet = $("header").outerHeight();
  //   const section = $(id).offset();
  //   const targetOffset = section.top - offSet - number;
  //   $("html,body").animate({ scrollTop: targetOffset }, speed);
  // }

  // const mainElement = document.querySelector("main");
  // // function Noti({
  // //   icon = "success",
  // //   text,
  // //   title,
  // //   timer = 4000,
  // //   redirect = "",
  // // }) {
  // //   var noti_con = document.querySelector(".noti_con");
  // //   if (!noti_con) {
  // //     var noti_con = document.createElement("div");
  // //     noti_con.setAttribute("class", "noti_con");
  // //     mainElement.appendChild(noti_con);
  // //   }
  // //   var noti_alert = document.createElement("div");
  // //   var noti_icon = document.createElement("div");
  // //   var noti_process = document.createElement("div");
  // //   noti_icon.setAttribute("class", "noti_icon " + icon);
  // //   noti_alert.setAttribute("class", "noti_alert");
  // //   noti_process.setAttribute("class", "progress active " + icon);
  // //   noti_alert.innerHTML =
  // //     '<div class="message"><p class="text1">' +
  // //     title +
  // //     '</p><p class="text2">' +
  // //     text +
  // //     "</p></div>";
  // //   noti_alert.prepend(noti_icon);
  // //   noti_alert.prepend(noti_process);
  // //   noti_con.prepend(noti_alert);

  // //   if (icon == "success") {
  // //     // noti_icon.style.background = '#00b972';
  // //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="14" stroke-dashoffset="14" d="M8 12L11 15L16 10"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.2s" values="14;0"/></path></g></svg>`;
  // //   } else if (icon == "info") {
  // //     // noti_icon.style.background = '#0395FF';
  // //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="20" stroke-dashoffset="20" d="M8.99999 10C8.99999 8.34315 10.3431 7 12 7C13.6569 7 15 8.34315 15 10C15 10.9814 14.5288 11.8527 13.8003 12.4C13.0718 12.9473 12.5 13 12 14"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.4s" values="20;0"/></path></g><circle cx="12" cy="17" r="1" fill="currentColor" fill-opacity="0"><animate fill="freeze" attributeName="fill-opacity" begin="1s" dur="0.2s" values="0;1"/></circle></svg>`;
  // //   } else if (icon == "danger" || icon == "error") {
  // //     // noti_icon.style.background = '#FF032C';
  // //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="8" stroke-dashoffset="8" d="M12 12L16 16M12 12L8 8M12 12L8 16M12 12L16 8"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.2s" values="8;0"/></path></g></svg>`;
  // //   } else {
  // //     // noti_icon.style.background = '#00b972';
  // //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="14" stroke-dashoffset="14" d="M8 12L11 15L16 10"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.2s" values="14;0"/></path></g></svg>`;
  // //   }

  // //   setTimeout(() => {
  // //     noti_alert.classList.add("active");
  // //   }, 100);

  // //   setTimeout(() => {
  // //     noti_alert.classList.remove("active");
  // //   }, timer);

  // //   setTimeout(() => {
  // //     noti_alert.remove();
  // //   }, timer + 2000);
  // // }
  // // function success(text) {
  // //   Noti({
  // //     text: text,
  // //     icon: "success",
  // //     timer: 5000,
  // //   });
  // // }
  // // function info(text) {
  // //   Noti({
  // //     text: text,
  // //     icon: "info",
  // //     timer: 5000,
  // //   });
  // // }
  // // function danger(text) {
  // //   Noti({
  // //     text: text,
  // //     icon: "danger",
  // //     timer: 5000,
  // //   });
  // // }

  // /** Danh sách bài viết AJAX , danh sách bài viết có loadmore **/
  // function getEventPostListPaged(
  //   flag,
  //   $this,
  //   formdata,
  //   processing,
  //   action,
  //   paged = 1
  // ) {
  //   if (!processing.hasClass("loading")) {
  //     $.ajax({
  //       url: mona_ajax_url.ajaxURL,
  //       type: "post",
  //       data: {
  //         action: "mona_ajax_loadmore_events",
  //         formdata: formdata,
  //         paged: paged,
  //         action_layout: action,
  //         action_flag: flag,
  //       },
  //       error: function (request) {
  //         processing.removeClass("loading");
  //       },
  //       beforeSend: function (response) {
  //         processing.addClass("loading");
  //       },
  //       success: function (result) {
  //         if (result.success) {
  //           console.log("success");
  //           if (
  //             result.data.action_return == "reload" &&
  //             result.data.posts_html != ""
  //           ) {
  //             $this.find("#monaEventList").html(result.data.posts_html);
  //             scrollToID("." + result.data.scroll, 500, 200);
  //           } else if (
  //             result.data.action_return == "loadmore" &&
  //             result.data.posts_html != ""
  //           ) {
  //             $this.find(".eventLoadMoreJS").remove();
  //             $this.find("#monaEventList").append(result.data.posts_html);
  //           }
  //         }
  //         processing.removeClass("loading");
  //       },
  //     });
  //   }
  // }

  // $(document).ready(function () {
  //   $("#formEventPostAjax").keydown(function (event) {
  //     if (event.keyCode == 13) {
  //       event.preventDefault();
  //       return false;
  //     }
  //   });
  // });
  // $(document).on("submit", "#formEventPostAjax", function (e) {
  //   var $this = $(this);
  //   var formdata = $this.serialize();
  //   var processing = $this.find("#monaEventList");
  //   var layout = $this.data("layout");
  //   getEventPostListPaged(true, $this, formdata, processing, layout);
  // });

  // $(document).on("change", ".onChangePostAjax", function (e) {
  //   var $this = $(this).closest("form");
  //   var layout = $this.data("layout");
  //   var formdata = $(this).closest("form").serialize();
  //   var processing = $(this).closest("form").find("#monaPostsList");
  //   getEventPostListPaged(false, $this, formdata, processing, layout);
  // });
  // $(document).on("click", ".monaClickPostAjax", function (e) {
  //   var $this = $(this).closest("form");
  //   var layout = $this.data("layout");
  //   var formdata = $(this).closest("form").serialize();
  //   var processing = $(this).closest("form").find("#monaPostsList");
  //   getEventPostListPaged(false, $this, formdata, processing, layout);
  // });
  // $(document).on(
  //   "click",
  //   ".pagination-posts-ajax a.page-numbers",
  //   function (e) {
  //     e.preventDefault();
  //     var $this = $(this);
  //     var form = $this.closest("form");
  //     var pagination = $this.closest(".pagination-posts-ajax");
  //     var pagedText = $this.text();
  //     var paged = pagedText.match(/\d+/);
  //     if (!paged) {
  //       if (!$this.hasClass("next")) {
  //         var pagedCurrentText = parseInt(
  //           pagination.find(".page-numbers.current").text()
  //         );
  //         var paged = pagedCurrentText - 1;
  //       } else {
  //         var pagedCurrentText = parseInt(
  //           pagination.find(".page-numbers.current").text()
  //         );
  //         var paged = pagedCurrentText + 1;
  //       }
  //     } else {
  //       paged = paged[0];
  //     }
  //     var formdata = $this.closest("form").serialize();
  //     var processing = $this.closest(".monaPostsList");
  //     getEventPostListPaged(true, form, formdata, processing, "reload", paged);
  //   }
  // );
  // $(document).on("click", ".eventLoadMoreJS", function (e) {
  //   e.preventDefault();
  //   var $this = $(this);
  //   var paged = $this.data("paged");
  //   var form = $this.closest("form");
  //   var formdata = $this.closest("form").serialize();
  //   getEventPostListPaged(true, form, formdata, $this, "loadmore", paged);
  // });
  // $(document).on("click", ".eventLoadMoreJS", function (e) {
  //   e.preventDefault();
  //   var $this = $(this);
  //   var paged = $this.data("paged");
  //   var form = $this.closest("form");
  //   var formdata = $this.closest("form").serialize();
  //   getEventPostListPaged(true, form, formdata, $this, "loadmore", paged);
  // });

