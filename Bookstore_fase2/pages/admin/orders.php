<?php
require_once '../../include/template2.inc.php';
require_once '../../include/auth_functions.php';
require_once '../../dao/OrderDAO.php';

// Protezione area admin
require_service('admin_panel');

$orderDao = new OrderDAO();
$notificationHtml = '';

// Gestione aggiornamento stato
$allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'];

    if ($orderId && in_array($status, $allowedStatuses)) {
        if ($orderDao->updateStatus($orderId, $status)) {
            $notificationHtml = "<div class='alert alert-success m-3'>Stato dell'ordine aggiornato con successo.</div>";
        } else {
            $notificationHtml = "<div class='alert alert-danger m-3'>Errore durante l'aggiornamento dell'ordine.</div>";
        }
    }
}

$orders = $orderDao->getAllOrders();

$tpl = new Template('../../skins/admin/tabler/dtml/main');
$content = new Template('../../skins/admin/tabler/dtml/orders');

$ordersListHtml = "";
if (empty($orders)) {
    $ordersListHtml = "<tr><td colspan='6' class='text-center'>Nessun ordine trovato.</td></tr>";
} else {
    foreach ($orders as $order) {
        $id = htmlspecialchars($order['id']);
        $userDisplay = htmlspecialchars($order['username'] . ' (' . $order['email'] . ')');
        $total = number_format((float)$order['total_amount'], 2, ',', '.');
        $date = date('d/m/Y H:i', strtotime($order['created_at']));
        $status = $order['status'];
        
        // Colore badge in base allo stato
        $badgeClass = 'bg-secondary';
        switch ($status) {
            case 'pending': $badgeClass = 'bg-warning'; break;
            case 'processing': $badgeClass = 'bg-azure'; break;
            case 'shipped': $badgeClass = 'bg-purple'; break;
            case 'delivered': $badgeClass = 'bg-success'; break;
            case 'cancelled': $badgeClass = 'bg-danger'; break;
        }
        
        $statusBadge = "<span class='badge $badgeClass text-white'>" . strtoupper($status) . "</span>";

        // Creiamo la select per gli stati
        $selectOptions = "";
        foreach ($allowedStatuses as $s) {
            $selected = ($s === $status) ? 'selected' : '';
            $selectOptions .= "<option value='$s' $selected>" . strtoupper($s) . "</option>";
        }

        $ordersListHtml .= "<tr>
            <td><span class='text-muted'>#$id</span></td>
            <td>$userDisplay</td>
            <td>€ $total</td>
            <td>$date</td>
            <td>$statusBadge</td>
            <td>
                <form action='orders.php' method='POST' class='d-flex gap-2'>
                    <input type='hidden' name='order_id' value='$id'>
                    <select name='status' class='form-select form-select-sm'>
                        $selectOptions
                    </select>
                    <button type='submit' class='btn btn-sm btn-primary'>Aggiorna</button>
                </form>
            </td>
        </tr>";
    }
}

$content->setContent('orders_list_html', $ordersListHtml);
$content->setContent('notification_html', $notificationHtml);

$tpl->setContent('page_content', $content->get());
$tpl->setContent('admin_name', $_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

echo $tpl->get();
?>
