<?php
// Database connection
$conn = new mysqli("localhost", "todo_user", "Hasan123", "todo_list");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add task
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $sql = "INSERT INTO tasks (task_name) VALUES ('$task')";
    if ($conn->query($sql) === TRUE) {
        $task_id = $conn->insert_id;
        $conn->query("INSERT INTO task_status (task_id) VALUES ($task_id)");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Delete task
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $conn->query("DELETE FROM task_status WHERE task_id = $task_id");
    $conn->query("DELETE FROM tasks WHERE id = $task_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Toggle completion
if (isset($_GET['toggle_complete'])) {
    $task_id = $_GET['toggle_complete'];
    $conn->query("UPDATE task_status SET completed = NOT completed WHERE task_id = $task_id");
    echo json_encode(['success' => true]);
    exit();
}

// Fetch tasks
$result = $conn->query("SELECT t.id, t.task_name, ts.completed 
                        FROM tasks t
                        LEFT JOIN task_status ts ON t.id = ts.task_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>To-Do List</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(to right, #e2a9d7, #f3d6e4);
        margin: 0;
        padding: 0;
    }
    h1 {
        font-size: 36px;
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 50px 0;
        text-transform: uppercase;
    }
    .todo-container {
        background: #fff;
        border-radius: 15px;
        width: 60%;
        margin: 0 auto;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    form {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
    }
    input[type="text"] {
        flex: 1;
        height: 56px;
        border: none;
        outline: none;
        border-radius: 28px;
        background: #f7f7f7;
        padding: 0 20px;
        font-size: 18px;
    }
    button {
        height: 56px;
        padding: 0 26px;
        border: none;
        border-radius: 28px;
        background: #ff66b2;
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: 0.3s;
    }
    button:hover { background: #ff3385; }
    .todo-list {
        list-style: none;
        padding: 0;
        margin-top: 30px;
    }
    .todo-item {
        display: flex;
        align-items: center;
        font-size: 20px;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 25px;
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .checkbox {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #ff66b2;
        margin-right: 20px;
        cursor: pointer;
    }
    .checkbox.checked { background: #ff66b2; }
    .task-text.completed {
        text-decoration: line-through;
        color: #d1d1d1;
    }
    .delete-btn {
        background: #FC94AF;
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        margin-left: auto;
        transition: 0.3s;
    }
    .delete-btn:hover { background: #F25278; }
</style>
</head>
<body>
<h1>TODO</h1>
<div class="todo-container">
    <form method="POST">
        <input type="text" name="task" placeholder="Create a new todo" required>
        <button type="submit" name="add_task">Add Task</button>
    </form>
    <ul class="todo-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="todo-item" id="task-<?php echo $row['id']; ?>">
                    <div class="checkbox <?php echo $row['completed'] ? 'checked' : ''; ?>" 
                         onclick="toggleComplete(<?php echo $row['id']; ?>)"></div>
                    <span class="task-text <?php echo $row['completed'] ? 'completed' : ''; ?>">
                        <?php echo htmlspecialchars($row['task_name'], ENT_QUOTES); ?>
                    </span>
                    <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn">Delete</a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>No tasks found!</li>
        <?php endif; ?>
    </ul>
</div>

<script>
function toggleComplete(taskId) {
    fetch('?toggle_complete=' + taskId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const taskElement = document.getElementById('task-' + taskId);
                const checkbox = taskElement.querySelector('.checkbox');
                const taskText = taskElement.querySelector('.task-text');
                checkbox.classList.toggle('checked');
                taskText.classList.toggle('completed');
            }
        });
}
</script>
</body>
</html>
