// import data from "../../../countries.json" assert { type: "json" };

export default function AcccountModule() {
  // const mainElement = document.querySelector("main");
  var progress = null;
  // function Noti({
  //   icon = "success",
  //   text,
  //   title,
  //   timer = 4000,
  //   redirect = "",
  // }) {
  //   var noti_con = document.querySelector(".noti_con");
  //   if (!noti_con) {
  //     var noti_con = document.createElement("div");
  //     noti_con.setAttribute("class", "noti_con");
  //     mainElement.appendChild(noti_con);
  //   }
  //   var noti_alert = document.createElement("div");
  //   var noti_icon = document.createElement("div");
  //   var noti_process = document.createElement("div");
  //   noti_icon.setAttribute("class", "noti_icon " + icon);
  //   noti_alert.setAttribute("class", "noti_alert");
  //   noti_process.setAttribute("class", "progress active " + icon);
  //   noti_alert.innerHTML =
  //     '<div class="message"><p class="text1">' +
  //     title +
  //     '</p><p class="text2">' +
  //     text +
  //     "</p></div>";
  //   noti_alert.prepend(noti_icon);
  //   noti_alert.prepend(noti_process);
  //   noti_con.prepend(noti_alert);
  //   if (icon == "success") {
  //     // noti_icon.style.background = '#00b972';
  //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="14" stroke-dashoffset="14" d="M8 12L11 15L16 10"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.2s" values="14;0"/></path></g></svg>`;
  //   } else if (icon == "info") {
  //     // noti_icon.style.background = '#0395FF';
  //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="20" stroke-dashoffset="20" d="M8.99999 10C8.99999 8.34315 10.3431 7 12 7C13.6569 7 15 8.34315 15 10C15 10.9814 14.5288 11.8527 13.8003 12.4C13.0718 12.9473 12.5 13 12 14"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.4s" values="20;0"/></path></g><circle cx="12" cy="17" r="1" fill="currentColor" fill-opacity="0"><animate fill="freeze" attributeName="fill-opacity" begin="1s" dur="0.2s" values="0;1"/></circle></svg>`;
  //   } else if (icon == "danger" || icon == "error") {
  //     // noti_icon.style.background = '#FF032C';
  //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="8" stroke-dashoffset="8" d="M12 12L16 16M12 12L8 8M12 12L8 16M12 12L16 8"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.2s" values="8;0"/></path></g></svg>`;
  //   } else {
  //     // noti_icon.style.background = '#00b972';
  //     noti_icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path stroke-dasharray="60" stroke-dashoffset="60" d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.5s" values="60;0"/></path><path stroke-dasharray="14" stroke-dashoffset="14" d="M8 12L11 15L16 10"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.2s" values="14;0"/></path></g></svg>`;
  //   }
  //   setTimeout(() => {
  //     noti_alert.classList.add("active");
  //   }, 100);
  //   setTimeout(() => {
  //     noti_alert.classList.remove("active");
  //   }, timer);
  //   setTimeout(() => {
  //     noti_alert.remove();
  //   }, timer + 2000);
  // }
  // function success(text) {
  //   Noti({
  //     text: text,
  //     icon: "success",
  //     timer: 5000,
  //   });
  // }
  // function info(text) {
  //   Noti({
  //     text: text,
  //     icon: "info",
  //     timer: 5000,
  //   });
  // }
  // function danger(text) {
  //   Noti({
  //     text: text,
  //     icon: "danger",
  //     timer: 5000,
  //   });
  // }
  $(document).on("change", ".monaField", function (e) {
    e.preventDefault();
    var $this = $(this);
    var value = $(this).val();
    if (value) {
      $this.closest(".form-ip-ip").find(".mona-error").fadeOut();
      //$this.closest('.monaFieldItem').find('.mona-error').html('');
    }
  });
  // submit register
  $(document).on("submit", "#formRegister", function (e) {
    e.preventDefault();
    var $this = $(this);
    var processing = $this;
    var formData = new FormData($this[0]);
    var check = $('[name="rule_check"]');
    if (check.length) {
      if (!check.is(":checked")) {
        if (typeof noti !== "undefined" && noti !== null) {
          console.log(123);
          $(".mona-error.mona-error-rule-check").html(noti.rule);
          $(".mona-error.mona-error-rule-check").fadeIn();
          return;
        }
      }
    }
    $(".mona-error.mona-error-rule-check").html();
    $(".mona-error.mona-error-rule-check").fadeOut();
    if (!processing.hasClass("loading")) {
      formData.append("action", "mona_ajax_register");
      jQuery.ajax({
        url: mona_ajax_url.ajaxURL,
        type: "post",
        data: formData,
        processData: false, // Important: Don't process the data
        contentType: false, // Important: Set content type to false
        error: function (request) {
          processing.removeClass("loading");
          $(".mona-notice").remove();
        },
        beforeSend: function () {
          $(".mona-error").fadeOut();
          $(".mona-notice").remove();
          processing.addClass("loading");
        },
        success: function (result) {
          if (result.success) {
            // Noti({
            //     text: result.data.message,
            //     title: result.data.title,
            //     icon: 'success',
            //     timer: 5000
            // })

            if (result.data.redirect != "") {
              if (!$this.hasClass("tab-login")) {
                window.location.href = result.data.redirect;
              }
            }
          } else {
            processing.addClass("loading");
            if (result.data.error) {
              $.each(result.data.error, function (key, val) {
                $("." + key).html(val);
                $("." + key).fadeIn();
              });
            }
          }
          processing.removeClass("loading");
        },
      });
    }
  });
  // submit login
  $(document).on("submit", "#formLogin", function (e) {
    e.preventDefault();
    var $this = $(this);
    var processing = $this;
    var formData = new FormData($this[0]);
    if (!processing.hasClass("loading")) {
      formData.append("action", "mona_ajax_login");
      jQuery.ajax({
        url: mona_ajax_url.ajaxURL,
        type: "post",
        data: formData,
        processData: false, // Important: Don't process the data
        contentType: false, // Important: Set content type to false
        error: function (request) {
          processing.removeClass("loading");
          $(".mona-notice").remove();
          $(".mona-error-primary").fadeOut();
        },
        beforeSend: function () {
          $(".mona-error").fadeOut();
          $(".mona-error-primary").fadeOut();
          $(".mona-notice").remove();
          processing.addClass("loading");
        },
        success: function (result) {
          if (result.success) {
            // Noti({
            //     text: result.data.message,
            //     title: result.data.title,
            //     icon: 'success',
            //     timer: 5000
            // })
            if (result.data.redirect != "") {
              window.location.href = result.data.redirect;
            } else {
              window.location.reload();
            }
          } else {
            if (result.data.error) {
              $.each(result.data.error, function (key, val) {
                $("." + key).html(val);
                $("." + key).fadeIn();
              });
            }
          }
          processing.removeClass("loading");
        },
      });
    }
  });
  $("#tabRegister").on("click", function (e) {
    e.preventDefault();
    $(".news_login--rt-item").removeClass("actived");
    $(".news_login--rt-item").eq(1).addClass("actived");
    // $("#formLogin").fadeOut();
    $(".mona-error").fadeOut();
    // $("#formRegister").fadeIn();
  });
  $("#tabLogin").on("click", function (e) {
    e.preventDefault();
    $(".news_login--rt-item").removeClass("actived");
    $(".news_login--rt-item").eq(0).addClass("actived");
    // $("#formRegister").fadeOut();
    $(".mona-error").fadeOut();

    // $("#formLogin").fadeIn();
  });
  $("#formUser input, #formUser select").on("change", function () {
    $(".btn.deactive").removeClass("deactive");
  });
  // submit forgot
  $(document).on("submit", "#formForgot", function (e) {
    e.preventDefault();
    var $this = $(this);
    var processing = $this;
    var formData = new FormData($this[0]);
    if (!processing.hasClass("processing")) {
      formData.append("action", "mona_ajax_forgot");
      jQuery.ajax({
        url: mona_ajax_url.ajaxURL,
        type: "post",
        data: formData,
        processData: false, // Important: Don't process the data
        contentType: false, // Important: Set content type to false
        error: function (request) {
          processing.removeClass("processing");
          $(".mona-notice").remove();
          $(".mona-error-primary").fadeOut();
        },
        beforeSend: function () {
          $(".mona-error").fadeOut();
          $(".mona-error-primary").fadeOut();
          $(".mona-notice").remove();
          processing.addClass("processing");
        },
        success: function (result) {
          if (result.success) {
            // Noti({
            //     text: result.data.message,
            //     title: result.data.title,
            //     icon: 'success',
            //     timer: 5000
            // })
            $(".monaReturnMessageForgot").html(
              '<div class="mona-notice mona-success">' +
                result.data.message +
                "</div>"
            );
          } else {
            if (result.data.error) {
              $.each(result.data.error, function (key, val) {
                $("." + key).html(val);
                $("." + key).fadeIn();
              });
            }
          }
          processing.removeClass("processing");
        },
      });
    }
  });
  // submit reset
  $(document).on("submit", "#formReset", function (e) {
    e.preventDefault();
    var $this = $(this);
    var processing = $this;
    var formData = new FormData($this[0]);
    if (!processing.hasClass("processing")) {
      formData.append("action", "mona_ajax_reset_password");
      jQuery.ajax({
        url: mona_ajax_url.ajaxURL,
        type: "post",
        data: formData,
        processData: false, // Important: Don't process the data
        contentType: false, // Important: Set content type to false
        error: function (request) {
          processing.removeClass("processing");
          $(".mona-notice").remove();
          $(".mona-error-primary").fadeOut();
        },
        beforeSend: function () {
          $(".mona-error").fadeOut();
          $(".mona-error-primary").fadeOut();
          $(".mona-notice").remove();
          processing.addClass("processing");
        },
        success: function (result) {
          if (result.success) {
            // Noti({
            //   text: result.data.message,
            //   title: result.data.title,
            //   icon: "success",
            //   timer: 5000,
            // });
            Swal.fire({
              customClass: {
                container: "my-swal",
              },
              title: result.data.title,
              text: result.data.message,
              icon: "success",
              timer: 5000,
              confirmButtonColor: "#000000",
              focusConfirm: false,

            });
            if (result.data.redirect != "") {
              window.location.href = result.data.redirect;
            }
          } else {
            if (result.data.error) {
              $.each(result.data.error, function (key, val) {
                $("." + key).html(val);
                $("." + key).fadeIn();
              });
            }
          }
          processing.removeClass("processing");
        },
      });
    }
  });
  $(document).on("submit", "#formUser", function (e) {
    e.preventDefault();
    var $this = $(this);
    var form = $this.serialize();
    var processing = $(".is-loading-btn");
    var form_data = new FormData();
    form_data.append("action", "mona_ajax_update_account");
    if ($("#fileUpload").prop("files").length > 0) {
      var file_data = $("#fileUpload").prop("files")[0];
      form_data.append("mona_user_avatar", file_data);
    }
    form_data.append("formdata", form);
    if (!processing.hasClass("loading")) {
      $.ajax({
        url: mona_ajax_url.ajaxURL,
        contentType: false,
        processData: false,
        type: "post",
        data: form_data,
        error: function (request) {
          $(".mona-notice").remove();
          processing.removeClass("loading");
        },
        beforeSend: function (response) {
          $(".mona-error").fadeOut();
          $(".mona-notice").remove();
          processing.addClass("loading");
        },
        success: function (result) {
          if (result.success) {
            console.log("success");
            // Noti({
            //   text: result.data.message,
            //   title: result.data.title,
            //   icon: "success",
            //   timer: 5000,
            // });
            Swal.fire({
              customClass: {
                container: "my-swal",
              },
              title: result.data.title,
              text: result.data.message,
              icon: "success",
              timer: 2500,
              confirmButtonColor: "#000000",
              focusConfirm: false,

            });
            if (result.data.redirect != "") {
              window.location.href = result.data.redirect;
            } else {
              setTimeout(() => {
                window.location.reload();
              }, 2500);
            }
          } else {
            if (result.data.error) {
              $.each(result.data.error, function (key, val) {
                $("." + key).html(val);
                $("." + key).fadeIn();
              });
            }
          }
          processing.removeClass("loading");
        },
      });
    }
  });

  $(document).on("click", "#clearBtn", function (e) {
    e.preventDefault();
    window.location.reload();
  });

  $(".monaWishListJS").on("click", function (e) {
    var $this = $(this);
    if ($this.hasClass("detail")) {
      var input = $(".control-1 .monaWishListJS input");
      console.log(123);
    } else {
      var input = $this.find("input");
    }
    var act = $this.data("act");
    var product_id = $this.data("id");
    if (progress) {
      progress.abort();
    }
    progress = $.ajax({
      url: mona_ajax_url.ajaxURL,
      type: "post",
      data: {
        action: "mona_ajax_wishlist",
        act: act,
        product_id: product_id,
      },

      success: function (result) {
        if (act == "add") {
          $this.data("act", "remove");
          input.prop("checked", true);
        } else {
          $this.data("act", "add");
          input.prop("checked", false);
        }
        if ($this.hasClass("del")) {
          window.location.reload();
        }
        console.log("success");
      },
    });
  });
  function format(item, state) {
    if (!item.id) {
      return item.text;
    }
    // var iso2 = $(item.element).data("value");
    // var flagUrl = data.data.find((country) => country.iso2 === iso2)?.flag;
    var countryUrl = "https://hatscripts.github.io/circle-flags/flags/";
    var stateUrl = "https://oxguy3.github.io/flags/svg/us/";
    var url = state ? stateUrl : countryUrl;
    var img = $("<img>", {
      class: "img-flag",
      width: 26,
      // src: flagUrl,
      src: url + item.element.dataset.value.toLowerCase() + ".svg",
    });
    var span = $("<span>", {
      text: " " + item.text,
    });
    span.prepend(img);
    return span;
  }
  $(document).ready(function () {
    // console.log(data);
    $("#countries").select2({
      templateResult: function (item) {
        return format(item, false);
      },
    });
    if (typeof label !== "undefined" && label !== null) {
      $('[data-provider="facebook"] .nsl-button-label-container').before(
        '<div class="name-provider">' + label.fb + "</div>"
      );
    }
  });

  $(document).on("click", ".ask-login", function (e) {
    e.preventDefault();
    console.log(noti);
    if (typeof noti !== "undefined" && noti !== null) {
      // if (!$(".noti_alert").hasClass("active")) {
      //   Noti({
      //     text: noti.message,
      //     title: noti.title,
      //     icon: noti.icon,
      //     timer: noti.timer,
      //   });
      // }
      if (!Swal.isVisible()) {
        Swal.fire({
          customClass: {
            container: "my-swal",
          },
          title: noti.title,
          text: noti.message,
          icon: noti.icon,
          timer: noti.timer,
          confirmButtonColor: "#000000",
          focusConfirm: false,

        });
      }
    }
  });
}
