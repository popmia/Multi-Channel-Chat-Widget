jQuery(document).ready(function ($) {
    const data = mcChatData;

    const whatsappNumber = data.whatsapp_number || '';
    const greetingText = data.greeting_text || '';
    const customIntro = data.custom_message_text || '';

    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    const showWhatsapp = data.show_whatsapp === '1' && whatsappNumber;
    const showFacebook = data.show_facebook === '1' && data.facebook_username.trim() !== '';
    const showGmail = !isMobile && data.show_gmail === '1';
    const showHotmail = !isMobile && data.show_hotmail === '1';
    const showOutlook = !isMobile && data.show_outlook === '1';
    const hasAnyChannel = showWhatsapp || showGmail || showHotmail || showOutlook || showFacebook;

    if (!hasAnyChannel) return;

    const reasons = Array.isArray(data.reasons) ? data.reasons : [];
    const t = data.i18n || {};

    const widgetHTML = `
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <div id="chat-float"><i class="fas fa-comments"></i></div>
        <div id="input-menu" class="popup-menu">
            <div class="chat-input-field"><input type="text" id="chat-user-name" placeholder="${t.name || 'Your Name'}" /></div>
            <div class="chat-input-field"><input type="text" id="chat-user-phone" placeholder="${t.phone || 'Phone Number'}" /></div>
            <div class="chat-option" id="proceed-button">${t.continue}</div>
        </div>
        <div id="reason-menu" class="popup-menu"></div>
        <div id="channel-menu" class="popup-menu"></div>
        <div id="chat-greeting">${$('<div>').text(greetingText).html()}</div>
    `;

    $('#mc-chat-widget').html(widgetHTML);

    let selectedReason = '';
    let userName = '';
    let userPhone = '';

    function proceedToReason() {
        userName = $('#chat-user-name').val().trim();
        userPhone = $('#chat-user-phone').val().trim();

        if (!userName || !userPhone) {
            alert(t.name_required || 'Please enter both your name and phone number.');
            return;
        }

        $('.popup-menu').removeClass('active').hide();

        if (reasons.length > 0) {
            const reasonOptions = reasons.map(label =>
                `<div class='chat-option' data-reason="${$('<div>').text(label).html()}">${$('<div>').text(label).html()}</div>`
            );
            $('#reason-menu').html(reasonOptions.join('')).addClass('active').show();
        } else {
            selectedReason = t.general_inquiry || 'General Inquiry';
            showChannels();
        }
    }

    function showChannels() {
        $('.popup-menu').removeClass('active').hide();

        const email = data.email_address.trim();
        if (!email && (showGmail || showHotmail || showOutlook)) {
            alert(t.email_missing || 'Email address is not configured.');
            return;
        }

        const options = [];
        if (showWhatsapp) options.push(`<div class='chat-option whatsapp' data-platform="whatsapp">💬 ${t.whatsapp}</div>`);
        if (showGmail) options.push(`<div class='chat-option' data-platform="gmail">📧 ${t.gmail}</div>`);
        if (showHotmail) options.push(`<div class='chat-option' data-platform="hotmail">📧 ${t.hotmail}</div>`);
        if (showOutlook) options.push(`<div class='chat-option' data-platform="outlook">📧 ${t.outlook}</div>`);
        if (showFacebook) {
            const fbURL = `https://m.me/${encodeURIComponent(data.facebook_username)}`;
            options.push(`<div class='chat-option' onclick='window.open("${fbURL}", "_blank")'>💬 ${t.facebook}</div>`);
        }
        if (reasons.length > 0) {
            options.push(`<div class='chat-option' id='back-button'>🔙 ${t.back}</div>`);
        }

        $('#channel-menu').html(options.join('')).addClass('active').show();
    }

    function sendVia(platform) {
        const email = data.email_address.trim();
        const path = window.location.hostname.replace(/^www\./, '') + window.location.pathname;
        const userInfo = `Name: ${userName}\nPhone: ${userPhone}`;
        const body = encodeURIComponent(`${userInfo}\n${customIntro}\nPage: ${path}\n\nMy request: ${selectedReason}`);
        const subject = encodeURIComponent(`${t.inquiry_subject || 'Inquiry from'} ${userName} ${userPhone}`);
        let url = '';

        switch (platform) {
            case 'whatsapp':
                url = `https://wa.me/${whatsappNumber}?text=${body}`;
                break;
            case 'gmail':
                url = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${subject}&body=${body}`;
                break;
            case 'hotmail':
                url = `https://outlook.live.com/mail/deeplink/compose?to=${email}&subject=${subject}&body=${body}`;
                break;
            case 'outlook':
                url = `https://outlook.office.com/mail/deeplink/compose?to=${email}&subject=${subject}&body=${body}`;
                break;
        }

        if (url) window.open(url, '_blank');
        $('.popup-menu').removeClass('active').hide();
    }

    $('#mc-chat-widget').on('click', '#chat-float', function () {
        const inputMenu = $('#input-menu');
        const isOpen = inputMenu.hasClass('active');
        $('#chat-greeting').addClass('hide');

        $('.popup-menu').removeClass('active').hide(); // Always close all

        if (!isOpen) {
            inputMenu.addClass('active').show();
        }
    });

    $('#mc-chat-widget').on('click', '#proceed-button', proceedToReason);

    $('#mc-chat-widget').on('click', '#reason-menu .chat-option', function () {
        selectedReason = $(this).data('reason');
        $('.popup-menu').removeClass('active').hide();
        showChannels();
    });

    $('#mc-chat-widget').on('click', '#channel-menu .chat-option', function () {
        const platform = $(this).data('platform');
        if (platform) sendVia(platform);
    });

    $('#mc-chat-widget').on('click', '#back-button', function () {
        $('.popup-menu').removeClass('active').hide();
        $('#reason-menu').addClass('active').show();
    });

    setTimeout(() => $('#chat-greeting').addClass('hide'), 15000);
});
