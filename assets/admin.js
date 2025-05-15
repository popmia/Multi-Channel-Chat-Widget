document.addEventListener('DOMContentLoaded', function () {
    const fbCheck = document.querySelector('input[name="mc_chat_show_facebook"]');
    const fbInput = document.querySelector('input[name="mc_chat_facebook_username"]');
    const fbWarning = document.querySelector('.fb-warning');

    const emailInput = document.querySelector('input[name="mc_chat_email_address"]');
    const emailWarning = document.querySelector('.email-warning');
    const emailToggles = document.querySelectorAll('.email-toggle');

    function validateEmailToggle() {
        const hasEmailEnabled = Array.from(emailToggles).some(el => el.checked);
        const emailEmpty = !emailInput.value.trim();
        emailWarning.style.display = (hasEmailEnabled && emailEmpty) ? 'block' : 'none';
    }

    function validateFacebookToggle() {
        const fbEnabled = fbCheck.checked;
        const fbEmpty = !fbInput.value.trim();
        fbWarning.style.display = (fbEnabled && fbEmpty) ? 'block' : 'none';
    }

    fbCheck?.addEventListener('change', validateFacebookToggle);
    fbInput?.addEventListener('input', validateFacebookToggle);
    emailInput?.addEventListener('input', validateEmailToggle);
    emailToggles?.forEach(el => el.addEventListener('change', validateEmailToggle));

    document.querySelector('form')?.addEventListener('submit', function (e) {
        let valid = true;

        if (fbCheck.checked && !fbInput.value.trim()) {
            fbWarning.style.display = 'block';
            alert(window.mcChatAdmin?.fbRequired || 'Please enter a Facebook username before enabling.');
            valid = false;
        }

        const emailRequired = Array.from(emailToggles).some(el => el.checked);
        if (emailRequired && !emailInput.value.trim()) {
            emailWarning.style.display = 'block';
            alert(window.mcChatAdmin?.emailRequired || 'Please enter an email address before enabling email channels.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
});
