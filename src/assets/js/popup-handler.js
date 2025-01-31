function openPopup(resultsContainer, resultsData) {
    const popup = document.getElementById('full-info-popup');
    if (popup) {
        popup.style.display = 'flex';

        const popupContent = `
            <div class="popup-content">
                <span class="close">&times;</span>
                <h3>Formulario de contacto</h3>
                <form id="lead-form">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" required>
                    <label for="company">Nombre de la empresa:</label>
                    <input type="text" id="company" name="company" required>
                    <label for="phone">Celular:</label>
                    <input type="text" id="phone" name="phone" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <div class="terms-container">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">Acepto las <a href="${seoTestingInclup.termsUrl}" target="_blank">políticas de tratamiento de datos</a> y <a href="${seoTestingInclup.privacyUrl}" target="_blank">términos y condiciones</a></label>
                    </div>
                    <button type="submit" class="button">Enviar</button>
                </form>
            </div>
        `;

        popup.innerHTML = popupContent;

        const closePopupButton = popup.querySelector('.close');
        if (closePopupButton) {
            closePopupButton.addEventListener('click', function() {
                popup.style.display = 'none';
            });
        }

        const leadForm = document.getElementById('lead-form');
        if (leadForm) {
            leadForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(leadForm);
                const leadData = {
                    name: formData.get('name'),
                    company: formData.get('company'),
                    phone: formData.get('phone'),
                    email: formData.get('email'),
                    domain: resultsData.url,
                    potential_client: resultsData.potential_client, // Utilizar directamente el campo del servidor
                    performanceScore: calculatePerformanceScore(resultsData.metrics),
                    accessibilityScore: calculateAccessibilityScore(resultsData.metrics),
                    seoScore: calculateSeoScore(resultsData.metrics)
                };

                // Enviar el formulario y almacenar el lead
                fetch(seoTestingInclup.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'seo_testing_inclup_save_lead',
                        lead: JSON.stringify(leadData)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        popup.style.display = 'none';
                        alert('Formulario enviado. Ahora puedes ver la información completa.');
                        displayAdditionalContent(resultsContainer, resultsData);

                        const viewMoreButton = document.getElementById('view-more-button');
                        if (viewMoreButton) {
                            viewMoreButton.style.display = 'none';
                        }
                    } else {
                        alert('Error al enviar el formulario. Inténtalo de nuevo más tarde.');
                    }
                })
                .catch(error => {
                    alert('Ocurrió un error. Por favor, intenta de nuevo.');
                    console.error('Error en la solicitud AJAX:', error);
                });
            });
        }
    }
}