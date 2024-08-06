$ = jQuery;

// Manipulate and move the error message on default WordPress login without having to rewrite a whole new custom login page
function elementReady(selector) {
  return new Promise((resolve, reject) => {
    const el = document.querySelector(selector);
    if (el) {
      resolve(el);
    }

    new MutationObserver((mutationRecords, observer) => {
      Array.from(document.querySelectorAll(selector)).forEach((element) => {
        resolve(element);
        observer.disconnect();
      });
    }).observe(document.documentElement, {
      childList: true,
      subtree: true,
    });
  });
}

elementReady("#login_error").then((element) => {
  if ($(".error-pw").length) {
    const pwMsg = $(".error-pw").detach();
    $(".wp-pwd").append(pwMsg);
  }
  if ($("#error-username").length) {
    const userMsg = $("#error-username").detach();
    $(userMsg).insertAfter("#user_login");
  }
  if ($(".error-email").length) {
    const emailMsg = $(".error-email").detach();
    $(emailMsg).insertAfter("#user_email");
  }
});

$(document).ready(function () {
  if ($(".wp-login-log-in").length) {
    $("p#nav").prepend("Already have an account?"); // a quick DOM way instead of filtering and string replacing
  }
  //simple remove vertical bar on login/registration screen without hacking the wp-login
  $("p#nav").html($("p#nav").html().replace("|", ""));

  //simple slider nothing fancy
  function slider(flag, num) {
    var current = $(".item.current"),
      next,
      index;
    if (!flag) {
      next = current.is(":last-child") ? $(".item").first() : current.next();
      index = next.index();
    } else if (flag === "dot") {
      next = $(".item").eq(num);
      index = num;
    } else {
      next = current.is(":first-child") ? $(".item").last() : current.prev();
      index = next.index();
    }
    next.addClass("current");
    current.removeClass("current");
    $(".dot").eq(index).addClass("current").siblings().removeClass("current");
  }
  var setSlider = setInterval(slider, 4000);

  $(".dot").on("click", function () {
    if ($(this).is(".current")) return;
    clearInterval(setSlider);
    var num = $(this).index();
    slider("dot", num);
    setSlider = setInterval(slider, 4000);
  });
});
