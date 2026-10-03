<?php 
$js_default_lang = htmlspecialchars($site_settings['default_language'] ?? 'en', ENT_QUOTES, 'UTF-8'); 
?>


<script type="text/javascript">
function googleTranslateElementInit() {
    const translateElement = document.querySelector('.google_translate_element');

    if (translateElement) {
        if (!translateElement.id) {
            translateElement.id = 'temp_translate_id';
        }
        
        new google.translate.TranslateElement({
            // This is the source language of your HTML, which should be 'en'
            pageLanguage: 'en', 
            autoDisplay: false,
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, translateElement.id);
    }

    // Wait until Google Translate applies translation
    const body = document.body;
    const checkTranslation = setInterval(() => {
        const iframe = document.querySelector('.goog-te-banner-frame, iframe.goog-te-menu-frame');
        const translated = document.querySelector('html.translated-ltr, html.translated-rtl');
        if (iframe || translated) {
            clearInterval(checkTranslation);
        }
    }, 200);
}
</script>

<script>
// Force the configured default language if no language cookie is present
(function() {
    var defaultLang = '<?php echo $js_default_lang; ?>'; // PHP variable is now used here
    var match = document.cookie.match(/googtrans=([^;]+)/);

    // Only force language if the cookie is NOT set
    if (!match) {
        var d = new Date();
        d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000)); 
        
        // Use the configured default language code
        document.cookie = 'googtrans=/en/' + defaultLang + '; expires=' + d.toUTCString() + '; path=/'; 
        window.location.hash = '#googtrans(/en/' + defaultLang + ')';
    }
})();
</script>

<script type="text/javascript" src="assets/js/element.js"></script>

<script>
// Hide Google Translate popup + tooltip permanently
(function() {
    const style = document.createElement('style');
    style.innerHTML = `
        div#goog-gt-tt, .VIpgJd-yAWNEb-L7lbkb {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
    `;
    document.head.appendChild(style);

    const observer = new MutationObserver(() => {
        document.querySelectorAll('#goog-gt-tt, .VIpgJd-yAWNEb-L7lbkb').forEach(el => {
            el.style.display = 'none';
            el.style.visibility = 'hidden';
            el.style.opacity = '0';
            el.style.pointerEvents = 'none';
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>

<script>
// Remove Google Translate top banner automatically
function deleteGoogleTranslateBanner() {
    const bannerFrame = document.querySelector('.goog-te-banner-frame.skiptranslate');
    if (bannerFrame) {
        bannerFrame.remove();
        document.body.style.top = '0px';
        document.body.style.paddingTop = '0px';
        document.body.style.marginTop = '0px';
        return true;
    }
    return false;
}

if ('MutationObserver' in window) {
    const observer = new MutationObserver(() => {
        if (deleteGoogleTranslateBanner()) observer.disconnect();
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

const aggressiveInterval = setInterval(() => {
    if (deleteGoogleTranslateBanner()) clearInterval(aggressiveInterval);
}, 100);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Note: The countryToLangMap is not fully used in the language forcing logic,
    // but I'll keep it here as it was in your original code.
    const countryToLangMap = {
        'KR':'ko','RU':'ru','UA':'uk','BY':'ru','KZ':'kk','BR':'pt','PT':'pt','ES':'es','MX':'es',
        'CO':'es','AR':'es','IT':'it','PL':'pl','ID':'id','FR':'fr','BE':'fr','CH':'fr',
        'TH':'th','VN':'vi','SA':'ar','EG':'ar','AE':'ar','MY':'ms','CN':'zh-CN',
        'HK':'zh-CN','TW':'zh-CN','TR':'tr','JP':'ja','IR':'fa','AF':'fa',
        'RS':'sr','RO':'ro','HR':'hr','IN':'hi','GR':'el','BD':'bn','PH':'fil',
        'KE':'sw','TZ':'sw','NL':'nl','BE':'nl','NG':'yo','ZA':'af','AZ':'az',
        'UZ':'uz','MN':'mn','GE':'ka','TG':'tg','YO':'yo','IG':'ig','HA':'ha','EN':'en'
    };

    function updateLangButtonText(code) {
        // Fallback to 'en' if code is unknown
        if (code === 'und' || !code) code = 'en'; 
        var nameElement = document.querySelector('[data-lang="' + code + '"]');
        var name = nameElement ? nameElement.textContent : 'English';
        document.querySelectorAll('.current-lang-text').forEach(el => {
            el.textContent = name;
        });
    }

    var defaultLang = '<?php echo $js_default_lang; ?>'; // Use PHP variable
    var match = document.cookie.match(/googtrans=([^;]+)/);

    // This block handles setting the language button text on page load
    if (match) {
        var rawCookie = match[1];
        var langCode = defaultLang;
        
        if (rawCookie.includes('|')) {
            langCode = rawCookie.split('|')[1] || defaultLang;
        } else if (rawCookie.includes('/')) {
            var parts = rawCookie.split('/');
            langCode = parts[parts.length - 1] || defaultLang;
        }
        
        updateLangButtonText(langCode);
    } else {
        // This is a redundant language force, but we update the code from 'lt'
        var d = new Date();
        d.setTime(d.getTime() + (24 * 60 * 60 * 1000));
        document.cookie = 'googtrans=/en/' + defaultLang + '; expires=' + d.toUTCString() + '; path=/';
        updateLangButtonText(defaultLang);
    }

    document.querySelectorAll('.lang-selector').forEach(selector => {
        selector.addEventListener('click', function(e) {
            e.preventDefault();
            var targetLang = this.getAttribute('data-lang');
            var originalLang = 'en';
            var hash = originalLang + '|' + targetLang;
            window.location.hash = '#googtrans(' + hash + ')';
            var d = new Date();
            d.setTime(d.getTime() + (24 * 60 * 60 * 1000));
            document.cookie = 'googtrans=/' + hash + '; expires=' + d.toUTCString() + '; path=/';
            location.reload();
        });
    });

    // Vanilla JS logic for dropdown
    const langTriggers = document.querySelectorAll('.lang-trigger');
    const dropdowns = document.querySelectorAll('.language-dropdown');

    langTriggers.forEach((langTrigger, index) => {
        const dropdown = dropdowns[index];
        if (langTrigger && dropdown) {
            langTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                if (dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                    this.classList.remove('open');
                } else {
                    // Close all other dropdowns first
                    dropdowns.forEach((d, i) => {
                        d.style.display = 'none';
                        if(langTriggers[i]) langTriggers[i].classList.remove('open');
                    });
                    
                    dropdown.style.display = 'block';
                    this.classList.add('open');
                }
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !langTrigger.contains(e.target)) {
                    dropdown.style.display = 'none';
                    langTrigger.classList.remove('open');
                }
            });
        }
    });
});
</script>


