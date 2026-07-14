/**
 * ============================================
 * Todos - Logica CRUD lato client
 * ============================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const todoList      = document.getElementById('todo-list');
    const todoForm      = document.getElementById('form-todo');
    const emptyState    = document.getElementById('empty-state');
    const todoCount     = document.getElementById('todo-count');
    const completedCount = document.getElementById('completed-count');

    let todos = [];

    // ---- Carica Todos ----
    async function loadTodos() {
        try {
            todoList.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';
            const data = await api.get('../api/todos/index.php');
            todos = data.data || [];
            renderTodos();
        } catch (error) {
            showAlert('todo-alerts', error.message, 'danger');
        }
    }

    // ---- Renderizza Lista ----
    function renderTodos() {
        if (todos.length === 0) {
            todoList.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            todoList.innerHTML = todos.map(todo => createTodoHTML(todo)).join('');
            attachTodoEvents();
        }
        updateCounts();
    }

    // ---- Crea HTML Todo ----
    function createTodoHTML(todo) {
        const isCompleted = parseInt(todo.completed) === 1;
        const date = new Date(todo.created_at).toLocaleDateString('it-IT', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        return `
            <li class="todo-item ${isCompleted ? 'completed' : ''}" data-id="${todo.id}">
                <button class="todo-checkbox ${isCompleted ? 'checked' : ''}" 
                        data-action="toggle" data-id="${todo.id}" 
                        title="${isCompleted ? 'Segna come non completato' : 'Segna come completato'}">
                    ${isCompleted ? '✓' : ''}
                </button>
                <div class="todo-content">
                    <div class="todo-title">${escapeHtml(todo.title)}</div>
                    ${todo.description ? `<div class="todo-desc">${escapeHtml(todo.description)}</div>` : ''}
                    <div class="todo-date">${date}</div>
                </div>
                <div class="todo-actions">
                    <button class="btn btn-ghost btn-icon" data-action="edit" data-id="${todo.id}" title="Modifica">✎</button>
                    <button class="btn btn-ghost btn-icon" data-action="delete" data-id="${todo.id}" title="Elimina">✕</button>
                </div>
            </li>
        `;
    }

    // ---- Aggiorna Contatori ----
    function updateCounts() {
        const total = todos.length;
        const completed = todos.filter(t => parseInt(t.completed) === 1).length;
        if (todoCount) todoCount.textContent = total;
        if (completedCount) completedCount.textContent = completed;
    }

    // ---- Attach Events ai Todo ----
    function attachTodoEvents() {
        // Toggle completamento
        todoList.querySelectorAll('[data-action="toggle"]').forEach(btn => {
            btn.addEventListener('click', () => toggleTodo(parseInt(btn.dataset.id)));
        });

        // Elimina
        todoList.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => deleteTodo(parseInt(btn.dataset.id)));
        });

        // Modifica
        todoList.querySelectorAll('[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', () => editTodo(parseInt(btn.dataset.id)));
        });
    }

    // ---- Crea Todo ----
    if (todoForm) {
        todoForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const titleInput = document.getElementById('todo-title');
            const descInput  = document.getElementById('todo-description');

            const title = titleInput.value.trim();
            const description = descInput.value.trim();

            if (!title) return;

            try {
                const data = await api.post('../api/todos/index.php', { title, description });
                todos.unshift(data.data);
                renderTodos();
                titleInput.value = '';
                descInput.value = '';
                showAlert('todo-alerts', 'Todo creato!', 'success', 2000);
            } catch (error) {
                showAlert('todo-alerts', error.message, 'danger');
            }
        });
    }

    // ---- Toggle Completamento ----
    async function toggleTodo(id) {
        const todo = todos.find(t => t.id == id);
        if (!todo) return;

        const newCompleted = parseInt(todo.completed) === 1 ? 0 : 1;

        try {
            const data = await api.put(`../api/todos/update.php?id=${id}`, { completed: newCompleted });
            const index = todos.findIndex(t => t.id == id);
            if (index !== -1) todos[index] = data.data;
            renderTodos();
        } catch (error) {
            showAlert('todo-alerts', error.message, 'danger');
        }
    }

    // ---- Elimina Todo ----
    async function deleteTodo(id) {
        if (!confirm('Sei sicuro di voler eliminare questo todo?')) return;

        try {
            await api.delete(`../api/todos/delete.php?id=${id}`);
            todos = todos.filter(t => t.id != id);
            renderTodos();
            showAlert('todo-alerts', 'Todo eliminato.', 'success', 2000);
        } catch (error) {
            showAlert('todo-alerts', error.message, 'danger');
        }
    }

    // ---- Modifica Todo (con modal) ----
    async function editTodo(id) {
        const todo = todos.find(t => t.id == id);
        if (!todo) return;

        // Crea modal
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3>Modifica Todo</h3>
                    <button class="btn-close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="edit-title">Titolo</label>
                        <input type="text" class="form-input" id="edit-title" value="${escapeHtml(todo.title)}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit-desc">Descrizione</label>
                        <textarea class="form-input" id="edit-desc">${escapeHtml(todo.description || '')}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost" data-dismiss="modal">Annulla</button>
                    <button class="btn btn-primary" id="btn-save-edit">Salva</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        // Chiudi modal
        const closeModal = () => backdrop.remove();
        backdrop.querySelectorAll('[data-dismiss="modal"]').forEach(el => {
            el.addEventListener('click', closeModal);
        });
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) closeModal();
        });

        // Salva
        document.getElementById('btn-save-edit').addEventListener('click', async () => {
            const newTitle = document.getElementById('edit-title').value.trim();
            const newDesc  = document.getElementById('edit-desc').value.trim();

            if (!newTitle) return;

            try {
                const data = await api.put(`../api/todos/update.php?id=${id}`, {
                    title: newTitle,
                    description: newDesc
                });
                const index = todos.findIndex(t => t.id == id);
                if (index !== -1) todos[index] = data.data;
                renderTodos();
                closeModal();
                showAlert('todo-alerts', 'Todo aggiornato!', 'success', 2000);
            } catch (error) {
                showAlert('todo-alerts', error.message, 'danger');
            }
        });
    }

    // ---- Utility: Escape HTML ----
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ---- Init ----
    loadTodos();
});
