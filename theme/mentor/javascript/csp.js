document.addEventListener("securitypolicyviolation", (e) => {

    $('iframe, img').each(function () {

        if ($(this).closest('.editor_atto_content').length > 0) {
            return;
        }

        if ($(this).attr('src').startsWith(e.blockedURI)) {
            $(this).replaceWith('<div class="csp-error alert alert-danger">Le contenu externe n\'est pas autorisé sur cette plateforme. Si vous estimez que ce contenu est' +
                ' légitime, veuillez contacter' +
                ' votre' +
                ' administrateur Mentor ou à l\'adresse fonctionnelle eformation.dgafp@finances.gouv.fr.</div>');
        }

    });
});
