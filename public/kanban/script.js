const API_URL = "/api/tasks";
const modal = document.getElementById("modalOverlay");
const createBtn = document.getElementById("createBtn");
const cancelBtn = document.getElementById("cancelBtn");
const titleInput = document.getElementById("taskTitle");
const descInput = document.getElementById("taskDesc");
const priorityInput = document.getElementById("taskPriority");

// Charger les tâches au chargement
document.addEventListener("DOMContentLoaded", loadTasks);

// Ouvrir le modal
document.getElementById("addTaskBtn").addEventListener("click", () => {
    titleInput.value = "";
    descInput.value = "";
    priorityInput.value = "medium";
    modal.classList.remove("hidden");
});

// Fermer le modal
cancelBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
});

// Créer une tâche
createBtn.addEventListener("click", async () => {
    const title = titleInput.value.trim();
    const description = descInput.value.trim();
    const priority = priorityInput.value;

    if (!title) {
        alert("Le titre est obligatoire.");
        return;
    }

    await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ title, description, priority })
    });

    modal.classList.add("hidden");
    loadTasks();
});

// Charger et afficher les tâches
async function loadTasks() {
    const response = await fetch(API_URL);
    const tasks = await response.json();

    document.getElementById("todo").innerHTML = "";
    document.getElementById("doing").innerHTML = "";
    document.getElementById("done").innerHTML = "";

    tasks.forEach(task => {
        const div = document.createElement("div");
        div.classList.add("task");
        div.draggable = true;
        div.dataset.id = task.id;

        // Badge de priorité
        const badge = document.createElement("span");
        badge.classList.add("priority-badge");
        badge.classList.add(`priority-${task.priority}`);
        badge.textContent =
            task.priority === "low" ? "Basse" :
            task.priority === "medium" ? "Moyenne" :
            "Haute";

        // Titre
        const title = document.createElement("span");
        title.textContent = task.title;
        title.classList.add("title");

        // Description
        const desc = document.createElement("p");
        desc.textContent = task.description;
        desc.classList.add("description");

        // Bouton supprimer
        const del = document.createElement("button");
        del.textContent = "✖";
        del.classList.add("delete-btn");
        del.addEventListener("click", () => deleteTask(task.id));

        // Drag
        div.addEventListener("dragstart", dragStart);

        // Ajouter les éléments
        div.appendChild(badge);
        div.appendChild(title);
        div.appendChild(desc);
        div.appendChild(del);

        document.getElementById(task.status).appendChild(div);
    });

    // Activer le drop
    document.querySelectorAll(".column").forEach(col => {
        col.addEventListener("dragover", dragOver);
        col.addEventListener("drop", dropTask);
    });
}

// Drag start
function dragStart(e) {
    e.dataTransfer.setData("text/plain", e.target.dataset.id);
}

// Autoriser le drop
function dragOver(e) {
    e.preventDefault();
}

// Drop dans une colonne
async function dropTask(e) {
    e.preventDefault();

    const taskId = e.dataTransfer.getData("text/plain");
    const newStatus = e.currentTarget.dataset.status;

    await fetch(`${API_URL}/${taskId}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status: newStatus })
    });

    loadTasks();
}

// Supprimer une tâche
async function deleteTask(id) {
    if (!confirm("Supprimer cette tâche ?")) return;

    await fetch(`${API_URL}/${id}`, {
        method: "DELETE"
    });

    loadTasks();
}
