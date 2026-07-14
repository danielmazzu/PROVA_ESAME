<?php
$pageTitle = 'To-Do List';
$pageScripts = ['../assets/js/todos.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
?>

<div class="page-header flex-between">
    <div>
        <h1>📝 To-Do List</h1>
        <p>
            <span id="todo-count">0</span> totali · 
            <span id="completed-count">0</span> completati
        </p>
    </div>
</div>

<div id="todo-alerts"></div>

<!-- Form Creazione -->
<div class="card mb-6">
    <div class="card-body">
        <form id="form-todo">
            <div class="form-inline">
                <div class="form-group" style="flex: 2;">
                    <label class="form-label" for="todo-title">Titolo</label>
                    <input type="text" class="form-input" id="todo-title" 
                           placeholder="Cosa devi fare?" required>
                </div>
                <div class="form-group" style="flex: 3;">
                    <label class="form-label" for="todo-description">Descrizione (opzionale)</label>
                    <input type="text" class="form-input" id="todo-description" 
                           placeholder="Aggiungi dettagli...">
                </div>
                <button type="submit" class="btn btn-primary btn-lg">+ Aggiungi</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista Todos -->
<div class="card">
    <ul class="todo-list" id="todo-list">
        <div class="loading-overlay"><div class="spinner"></div></div>
    </ul>

    <div id="empty-state" class="empty-state hidden">
        <div class="empty-state-icon">📝</div>
        <h3>Nessun Todo</h3>
        <p>Aggiungi il tuo primo todo usando il form qui sopra!</p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
