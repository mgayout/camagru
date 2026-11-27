
const photoGallery = document.getElementById('photoGallery');

async function loadPhotos() {
    try {
        const response = await fetch('/camagru/getPhotos');
        const data = await response.json();

        if (!response.ok || !data.success) {
            console.error(data.message || 'Impossible de charger les photos.');
            return;
        }

        photoGallery.innerHTML = '';

        if (data.photos.length === 0) {
            photoGallery.textContent = 'Aucune photo enregistrée.';
            return;
        }

        data.photos.forEach(photo => {
            const container = document.createElement('div');
            container.classList.add('saved-photo');

            const img = document.createElement('img');
            img.src = photo.url;
            img.alt = 'Photo enregistrée';

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.classList.add('delete-photo');
            deleteButton.textContent = '×';
            deleteButton.setAttribute('aria-label', 'Supprimer cette photo');

            deleteButton.addEventListener('click', (event) => {
                event.stopPropagation();
                deletePhoto(photo.id, container);
            });

            const date = document.createElement('p');
            date.classList.add('photo-date');
            date.textContent = new Date(photo.date).toLocaleString('fr-FR');

            container.appendChild(img);
            container.appendChild(deleteButton);
            container.appendChild(date);

            photoGallery.appendChild(container);
        });

    } catch (error) {
        console.error('Erreur lors du chargement des photos :', error);
    }
}

async function deletePhoto(photoId, container) {
    if (!confirm('Voulez-vous vraiment supprimer cette photo ?')) {
        return;
    }

    try {
        const response = await fetch('/camagru/deletePhoto', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: photoId,
                csrf_token: document.getElementById('csrf_token').value
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            alert(data.message || 'Erreur lors de la suppression.');
            return;
        }

        container.remove();

        if (photoGallery.querySelectorAll('.saved-photo').length === 0) {
            photoGallery.textContent = 'Aucune photo enregistrée.';
        }

    } catch (error) {
        console.error('Erreur lors de la suppression :', error);
        alert('Impossible de contacter le serveur.');
    }
}

loadPhotos();
