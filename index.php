<?php
require_once 'config.php';
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$login_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === AUTH_USERNAME && $_POST['password'] === AUTH_PASSWORD) {
            $_SESSION['loggedin'] = true;
            header('Location: index.php');
            exit;
        } else {
            $login_error = 'Invalid username or password.';
        }
    }
}

if (empty($_SESSION['loggedin'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Database Viewer</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="card login-card shadow-sm">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4"><i class="ri-database-2-line"></i> Database Viewer</h3>
            <?php if ($login_error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit;
}


try {
    $databases = getDatabases();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$selectedDb = $_GET['db'] ?? null;
$selectedTable = $_GET['table'] ?? null;

$tables = [];
$tableData = [];
$tableStructure = [];

if ($selectedDb) {
    if (!in_array($selectedDb, $databases)) {
        die("Error: Invalid database selected.");
    }
    try {
        $tables = getTables($selectedDb);
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

if ($selectedDb && $selectedTable) {
    if (!in_array($selectedTable, $tables)) {
        die("Error: Invalid table selected.");
    }

    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $result = getTableData($selectedDb, $selectedTable, $limit, $offset);
        $tableData = $result['data'];
        $totalRows = $result['total'];

        $tableStructure = getTableStructure($selectedDb, $selectedTable);
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="logo"><i class="ri-database-2-line"></i> Database Viewer</div>
            <a href="?logout=1" class="btn btn-sm btn-outline-danger" title="Logout"><i class="ri-logout-box-line"></i></a>
        </div>
                
                <div class="mb-4">
                    <h6 class="text-muted mb-2">Databases</h6>
                    <div class="list-group">
                        <?php foreach ($databases as $db): ?>
                            <?php if (!in_array($db, ['information_schema', 'performance_schema', 'mysql', 'sys'])): ?>
                                <a href="?db=<?php echo urlencode($db); ?>" 
                                   class="list-group-item list-group-item-action <?php echo $selectedDb === $db ? 'active-db' : ''; ?>">
                                    <i class="ri-database-2-line me-2"></i><?php echo htmlspecialchars($db); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if ($selectedDb && !empty($tables)): ?>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Tables in <?php echo htmlspecialchars($selectedDb); ?></h6>
                        <div class="list-group">
                            <?php foreach ($tables as $table): ?>
                                <a href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($table); ?>" 
                                   class="list-group-item list-group-item-action <?php echo $selectedTable === $table ? 'active-table' : ''; ?>">
                                    <i class="ri-table-line me-2"></i><?php echo htmlspecialchars($table); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
    <div class="main-content">
        <?php if (!$selectedDb): ?>
            <div class="text-center mt-5 welcome-message">
                <i class="ri-database-2-line mb-3"></i>
                <h3 class="text-muted">Welcome to Database Viewer</h3>
                <p class="text-muted">Select a database from the left sidebar to get started</p>
            </div>
        <?php elseif (!$selectedTable): ?>
            <div class="text-center mt-5 welcome-message">
                <i class="ri-table-line mb-3"></i>
                <h3 class="text-muted">Database: <?php echo htmlspecialchars($selectedDb); ?></h3>
                <p class="text-muted">Select a table to view its data</p>
                <div class="alert alert-info mt-3">
                    <strong>Tables found:</strong> <?php echo count($tables); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="ri-table-line me-2"></i><?php echo htmlspecialchars($selectedTable); ?></h3>
                <div>
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#structureModal">
                        <i class="ri-information-line"></i> Structure
                    </button>
                </div>
            </div>
                    
                    <div class="alert alert-info">
                        <strong>Database:</strong> <?php echo htmlspecialchars($selectedDb); ?> | 
                        <strong>Total Records:</strong> <?php echo isset($totalRows) ? number_format($totalRows) : 0; ?>
                    </div>
                    
                    <?php if (!empty($tableData)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <?php foreach (array_keys($tableData[0]) as $column): ?>
                                            <th scope="col"><?php echo htmlspecialchars($column); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tableData as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $value): ?>
                                                <td>
                                                    <?php 
                                                    if ($value === null) {
                                                        echo '<span class="text-muted">NULL</span>';
                                                    } elseif (is_string($value) && strlen($value) > 100) {
                                                        echo htmlspecialchars(substr($value, 0, 100)) . '...';
                                                    } else {
                                                        echo htmlspecialchars($value);
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (isset($totalRows) && $totalRows > $limit): ?>
                            <?php 
                            $totalPages = ceil($totalRows / $limit);
                            $currentPage = $page;
                            ?>
                            <nav aria-label="Table pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($selectedTable); ?>&page=<?php echo $currentPage - 1; ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($selectedTable); ?>&page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?db=<?php echo urlencode($selectedDb); ?>&table=<?php echo urlencode($selectedTable); ?>&page=<?php echo $currentPage + 1; ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="ri-error-warning-line"></i> No data found in this table.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($selectedTable && !empty($tableStructure)): ?>
        <div class="modal fade" id="structureModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Table Structure: <?php echo htmlspecialchars($selectedTable); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Type</th>
                                        <th>Null</th>
                                        <th>Key</th>
                                        <th>Default</th>
                                        <th>Extra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tableStructure as $column): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($column['Field']); ?></strong></td>
                                            <td><code><?php echo htmlspecialchars($column['Type']); ?></code></td>
                                            <td><?php echo $column['Null'] === 'YES' ? '<span class="text-success">YES</span>' : '<span class="text-danger">NO</span>'; ?></td>
                                            <td>
                                                <?php if ($column['Key']): ?>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($column['Key']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $column['Default'] ? htmlspecialchars($column['Default']) : '<span class="text-muted">NULL</span>'; ?></td>
                                            <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
