// JavaScript voor CRUD functionaliteit
let currentItemId = null;

// Modal functies
function openModal(action, itemId = null) {
    const modal = document.getElementById('itemModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('itemForm');
    const currentImageDiv = document.getElementById('currentImage');
    
    // Form resetten
    form.reset();
    currentImageDiv.innerHTML = '';
    currentItemId = itemId;
    
    if (action === 'add') {
        modalTitle.textContent = 'Nieuw Item Toevoegen';
        document.getElementById('itemId').value = '';
    } else if (action === 'edit' && itemId) {
        modalTitle.textContent = 'Item Bewerken';
        loadItemForEdit(itemId);
    }
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('itemModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    currentItemId = null;
}

// Item laden voor bewerking
async function loadItemForEdit(itemId) {
    try {
        showLoading();
        const response = await fetch(`api/items.php?id=${itemId}`);
        const result = await response.json();
        
        if (result.success) {
            const item = result.data;
            document.getElementById('itemId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('description').value = item.description || '';
            
            // Huidige afbeelding tonen
            if (item.image_path) {
                const currentImageDiv = document.getElementById('currentImage');
                currentImageDiv.innerHTML = `
                    <p><strong>Huidige afbeelding:</strong></p>
                    <img src="${item.image_path}" alt="Huidige afbeelding" style="max-width: 200px; max-height: 150px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                `;
            }
        } else {
            showAlert('Fout bij het laden van het item: ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('Er is een fout opgetreden: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Item bewerken
function editItem(itemId) {
    openModal('edit', itemId);
}

// Item verwijderen
async function deleteItem(itemId) {
    if (!confirm('Weet je zeker dat je dit item wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.')) {
        return;
    }
    
    try {
        showLoading();
        const response = await fetch('api/items.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: itemId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Item succesvol verwijderd!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('Fout bij het verwijderen: ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('Er is een fout opgetreden: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Form submit handler
document.getElementById('itemForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const itemId = formData.get('id');
    const isEdit = itemId && itemId !== '';
    
    try {
        showLoading();
        
        let response;
        if (isEdit) {
            // Voor updates gebruiken we PUT met JSON
            const data = {
                id: itemId,
                title: formData.get('title'),
                description: formData.get('description'),
                image_path: formData.get('image_path') || null
            };
            
            response = await fetch('api/items.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
        } else {
            // Voor nieuwe items gebruiken we POST met FormData
            response = await fetch('api/items.php', {
                method: 'POST',
                body: formData
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(isEdit ? 'Item succesvol bijgewerkt!' : 'Item succesvol toegevoegd!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('Fout: ' + result.message, 'error');
        }
    } catch (error) {
        showAlert('Er is een fout opgetreden: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
});

// Loading functies
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// Alert functie
function showAlert(message, type = 'info') {
    // Verwijder bestaande alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Alert styling
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 3000;
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideInRight 0.3s ease;
    `;
    
    // Kleuren per type
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    alert.style.backgroundColor = colors[type] || colors.info;
    
    // Sluit button styling
    const closeBtn = alert.querySelector('button');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
    `;
    
    document.body.appendChild(alert);
    
    // Auto verwijderen na 5 seconden
    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

// CSS animatie toevoegen
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);

// Modal sluiten bij klik buiten modal
window.addEventListener('click', function(event) {
    const modal = document.getElementById('itemModal');
    if (event.target === modal) {
        closeModal();
    }
});

// ESC toets om modal te sluiten
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Afbeelding preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const currentImageDiv = document.getElementById('currentImage');
            currentImageDiv.innerHTML = `
                <p><strong>Nieuwe afbeelding preview:</strong></p>
                <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            `;
        };
        reader.readAsDataURL(file);
    }
});
