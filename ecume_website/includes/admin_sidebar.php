<aside class="sidebar">
    <ul>
        <li><a href="../index.php">Member Page</a></li>
        <li><a href="admin_dashboard.php">Dashboard</a></li>

        <li class="submenu">
            <a href="#">User Management ▼</a>
            <ul class="submenu-list">
                <li><a href="edit_user.php">Manage Users</a></li>
                <!-- <li><a href="delete_user.php">Delete Users</a></li> -->
                <li><a href="manage_roles.php">Manage Roles</a></li>
            </ul>
        </li>

        <li class="submenu">
            <a href="#">Records Management ▼</a>
            <ul class="submenu-list">
                <li><a href="admin_search_record.php">Search Record</a></li>
                <li><a href="admin_manage_records.php">Manage Records</a></li>
            </ul>
        </li>

        <li class="submenu">
            <a href="#">Financial Management ▼</a>
            <ul class="submenu-list">
                <li><a href="admin_financial_records.php">Financial Records</a></li>
                <li><a href="admin_payments.php">View Payment Records</a></li>
            </ul>
        </li>

        <li class="submenu">
            <a href="#">Approvals & Uploads ▼</a>
            <ul class="submenu-list">
                <li><a href="admin_approve.php">Manage Approvals</a></li>
                <li><a href="admin_upload.php">Manage Upload</a></li>
            </ul>
        </li>

        <li><a href="settings.php">Settings</a></li>
    </ul>
</aside>

<style>
.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    padding: 10px;
}

.submenu-list {
    display: none;
    padding-left: 15px;
}

.submenu a {
    cursor: pointer;
    display: block;
    padding: 10px;
    background: #007bff;
    color: white;
    border-radius: 5px;
}

.submenu a:hover {
    background: #0056b3;
}

.submenu-list li a {
    display: block;
    padding: 8px;
    text-decoration: none;
    color: #333;
}

.submenu-list li a:hover {
    background: #f1f1f1;
    border-radius: 5px;
}
</style>

<script>
document.querySelectorAll('.submenu > a').forEach(menu => {
    menu.addEventListener('click', function(event) {
        event.preventDefault();
        let submenuList = this.nextElementSibling;
        submenuList.style.display = submenuList.style.display === 'block' ? 'none' : 'block';
    });
});
</script>


<!-- <aside class="sidebar">
    <ul>
        <li><a href="../index.php">Member Page</a></li>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="edit_user.php">Manage Users</a></li>
        <li><a href="delete_user.php">Delete Users</a></li>
        <li><a href="manage_roles.php">Manage Roles</a></li>
        <li><a href="admin_financial_records.php">Financial Records</a></li>
        <li><a href="admin_search_record.php">Search Record</a></li>
        <li><a href="admin_approve.php">Manage Approvals</a></li>
        <li><a href="admin_upload.php">Manage Upload</a></li>
        <li><a href="admin_manage_records.php">Manage Records</a></li>
        <li><a href="admin_payments.php">View Payment Records</a></li>
        <li><a href="settings.php">Settings</a></li>
    </ul>
</aside> -->

