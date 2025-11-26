// Afficher le popup quand le bouton de signalement est cliqué
document.querySelectorAll('.btn-signalement').forEach(function(button) {
    if (!button.hasAttribute('data-clicked')) {
        button.addEventListener('click', function() {
            var idProposition = this.getAttribute('data-idProposition');
            document.getElementById('signalementPopup-' + idProposition).style.display = 'block';
        });
        button.setAttribute('data-clicked', 'true');
    }
});

// fermer le popup
document.querySelectorAll('.close').forEach(function(closeButton) {
    if (!closeButton.hasAttribute('data-clicked')) {
        closeButton.addEventListener('click', function() {
            var idProposition = this.getAttribute('data-idProposition');
            document.getElementById('signalementPopup-' + idProposition).style.display = 'none';
        });
        closeButton.setAttribute('data-clicked', 'true');
    }
});

// Soumettre le formulaire et gérer la réponse
document.querySelectorAll('form[id^="popupForm-"]').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        var idProposition = this.querySelector('input[name="idProposition"]').value;
        var raison = this.querySelector('textarea[name="raison"]').value;
        if (raison.trim() === '') {
            alert('Veuillez saisir une raison.');
            return;
        }
        var submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        var idSignalement = 'signalement-' + idProposition;
        if (document.getElementById(idSignalement)) {
            alert("Vous avez déjà envoyé un signalement pour cette proposition.");
            document.getElementById('signalementPopup-' + idProposition).style.display = 'none';
            return;
        }
        var sentMarker = document.createElement('div');
        sentMarker.id = idSignalement;
        document.body.appendChild(sentMarker);
        var formData = new FormData(this);
        fetch('routeur.php?controleur=controleurSignalement&action=signalerProposition', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            alert('Votre signalement a été envoyé.');
            document.getElementById('signalementPopup-' + idProposition).style.display = 'none';
            var signalementButton = document.querySelector('.btn-signalement[data-idProposition="' + idProposition + '"]');
            if (signalementButton) {
                signalementButton.textContent = 'Signalé';
                signalementButton.disabled = true;
                signalementButton.classList.add('btn-disabled'); 
            }
            submitButton.disabled = false;
        })
        .catch(error => {
            console.error('Erreur lors de l\'envoi du signalement:', error);
            alert('Une erreur est survenue.');
            submitButton.disabled = false;
        });
    });
});
