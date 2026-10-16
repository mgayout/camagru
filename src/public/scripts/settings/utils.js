// console.log('UTILS.JS MODIFIÉ EST CHARGÉ');

export async function sendSettingsRequest(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    });

    const text = await response.text();

    console.log('URL :', url);
    console.log('Statut HTTP :', response.status);
    console.log('Réponse serveur :', text);

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
