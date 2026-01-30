function changeAvatar(imagePath) {
    document.getElementById('avatar').src = imagePath;
}

window.addEventListener('load', function() {
    const errorAlert = document.querySelector('.alert.alert-error');
    if(!errorAlert) return;

    changeAvatar('assets/img/fehler.png');
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    changeAvatar('assets/img/fehler.png');

    setTimeout(() => {
        changeAvatar('assets/img/normal.png');
    }, 3_000);
});




function oeffneNeueAufgabeModal() {
    document.getElementById('modalTitel').textContent = 'Neue Aufgabe';
    document.getElementById('formAktion').value = 'erstellen';
    document.getElementById('formId').value = '';
    document.getElementById('aufgabeForm').reset();
    document.getElementById('aufgabeModal').classList.add('active');
}

function schliesseModal() {
    document.getElementById('aufgabeModal').classList.remove('active');
}

function erledigeAufgabe(id) {
    if(!confirm('Möchten Sie diese Aufgabe als erledigt markieren?')) return;
    window.location.href = 'todo_actions.php?aktion=erledigen&id=' + id;
}

function oeffneAufgabe(id) {
    if (!confirm('Möchten Sie diese Aufgabe wieder öffnen?')) return;
    window.location.href = 'todo_actions.php?aktion=oeffnen&id=' + id;
}

function loescheAufgabe(id) {
    if (confirm('Möchten Sie diese Aufgabe wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) return;
    window.location.href = 'todo_actions.php?aktion=loeschen&id=' + id;
}

function bearbeiteAufgabe(id) {
    fetch('todo_actions.php?aktion=abrufen&id=' + id)
        .then(response => response.json())
        .then(data => {
            if(!data.erfolg){
                alert('Fehler beim Laden der Aufgabe.');
                return;
            }

            document.getElementById('modalTitel').textContent = 'Aufgabe bearbeiten';
            document.getElementById('formAktion').value = 'bearbeiten';
            document.getElementById('formId').value = data.todo.id;
            document.getElementById('titel').value = data.todo.titel;
            document.getElementById('beschreibung').value = data.todo.beschreibung || '';
            document.getElementById('prioritaet').value = data.todo.prioritaet;
            document.getElementById('faelligkeit').value = data.todo.faelligkeit || '';
            document.getElementById('aufgabeModal').classList.add('active');
        })
        .catch(error => {
            console.error('Fehler:', error);
            alert('Ein Fehler ist aufgetreten.');
        });
}

// Modal schließen bei Klick außerhalb
document.getElementById('aufgabeModal').addEventListener('click', function(e) {
    if(e.target !== this) return;
    schliesseModal();
});

// Modal schließen mit ESC-Taste
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    schliesseModal();
});