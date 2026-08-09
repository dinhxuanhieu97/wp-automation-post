// Toast function
export function toastAction({
    type = 'info',
    content = '',
    duration = 3000
}) {
    const main = document.getElementById('mona-toast');
    if (main) {
        const toast = document.createElement('div');

        // Auto remove toast
        const autoRemoveId = setTimeout(function() {
            main.removeChild(toast);
        }, duration + 1000);

        // Remove toast when clicked
        toast.onclick = function(e) {
            if (e.target.closest('.toast__close')) {
                main.removeChild(toast);
                clearTimeout(autoRemoveId);
            }
        };
        const delay = (duration / 1000).toFixed(2);

        toast.classList.add('toast-wrap', `toast--${type}`);
        toast.style.animation = `slideInLeft ease .3s, fadeOut linear 1s ${delay}s forwards`;

        toast.innerHTML = `${content}`;
        main.appendChild(toast);
    }
}

// insert html function
export function getErrorMessage($text) {
    var $message =
        '<div class="toast__icon"><span class="dashicons dashicons-info"></span></div>';
    $message += '<div class="toast__body">';
    $message += '<h3 class="toast__title">Thông báo!</h3>';
    $message += '<p class="toast__msg">' + $text + "</p>";
    $message += "</div>";
    $message +=
        '<div class="toast__close"><span class="dashicons dashicons-no"></span></div>';
    $message += '<div class="progress"></div>';
    return $message;
}

// error message function
export function insertStringValue(objectData) {
    if (!$.isEmptyObject(objectData)) {
        $.each(objectData, function(objKey, objKeyValue) {
            if (objKeyValue != "") {
                $(objKey).html(objKeyValue);
            }
        });
    }
}