console.log("Arawan E-Bayanan loaded")

document.addEventListener("DOMContentLoaded", function () {
    const successAlert = document.getElementById("successAlert");
  
    if (successAlert) {
      setTimeout(function () {
        successAlert.classList.add("hide");
  
        setTimeout(function () {
          successAlert.remove();
  
          const url = new URL(window.location.href);
          url.searchParams.delete("submitted");
          window.history.replaceState({}, document.title, url.pathname + url.search);
        }, 400);
      }, 5000);
    }
  });