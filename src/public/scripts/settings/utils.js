export async function sendSettingsRequest(url, data) {

    const csrfToken = document.getElementById('csrf_token')?.value;

    if (!csrfToken) {
        throw new Error('Token CSRF introuvable.');
    }

    const formData = new URLSearchParams({
        ...data,
        csrf_token: csrfToken
    });

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    });

    const text = await response.text();

    let result;

    try {
        result = JSON.parse(text);
    } catch {
        throw new Error('Le serveur ne renvoie pas du JSON valide.');
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Échec de la mise à jour');
    }

    return result;
}