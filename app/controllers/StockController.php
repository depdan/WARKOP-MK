<?php
declare(strict_types=1);

class StockController
{
    public static function products(): void
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireRole('admin');
            verifyCsrf();

            $action = $_POST['action'] ?? '';

            try {
                if ($action === 'create') {
                    self::validateProductInput();

                    StockModel::createProduct([
                        'category_id' => (int)($_POST['category_id'] ?? 0),
                        'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
                        'sku' => trim($_POST['sku'] ?? ''),
                        'name' => trim($_POST['name'] ?? ''),
                        'unit' => trim($_POST['unit'] ?? 'pcs'),
                        'cost_price' => (float)($_POST['cost_price'] ?? 0),
                        'sell_price' => (float)($_POST['sell_price'] ?? 0),
                        'stock' => (float)($_POST['stock'] ?? 0),
                        'min_stock' => (float)($_POST['min_stock'] ?? 0),
                    ], user()['id']);

                    flash('success', 'Produk berhasil ditambahkan.');

                } elseif ($action === 'update') {
                    self::validateProductInput();

                    $id = (int)$_POST['id'];

                    StockModel::updateProduct($id, [
                        'category_id' => (int)($_POST['category_id'] ?? 0),
                        'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
                        'sku' => trim($_POST['sku'] ?? ''),
                        'name' => trim($_POST['name'] ?? ''),
                        'unit' => trim($_POST['unit'] ?? 'pcs'),
                        'cost_price' => (float)($_POST['cost_price'] ?? 0),
                        'sell_price' => (float)($_POST['sell_price'] ?? 0),
                        'min_stock' => (float)($_POST['min_stock'] ?? 0),
                        'is_active' => (int)($_POST['is_active'] ?? 1),
                    ], user()['id']);

                    flash('success', 'Produk berhasil diperbarui.');

                } elseif ($action === 'delete') {
                    $id = (int)$_POST['id'];
                    StockModel::deleteProduct($id, user()['id']);
                    flash('success', 'Produk berhasil dihapus.');

                } elseif ($action === 'restore') {
                    $id = (int)$_POST['id'];
                    StockModel::restoreProduct($id, user()['id']);
                    flash('success', 'Produk berhasil dipulihkan.');
                }

            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }

            redirect(BASE_URL . '/?page=products');
        }

        $products = StockModel::products(
            trim($_GET['q'] ?? '')
        );

        $categories = StockModel::categories();
        $suppliers = StockModel::suppliers();

        $editProduct = null;

        if (!empty($_GET['edit'])) {
            $editProduct = StockModel::findProduct(
                (int)$_GET['edit']
            );
        }

        require __DIR__ . '/../views/products.php';
    }

    private static function validateProductInput(): void
    {
        $sku = trim($_POST['sku'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');

        if ($sku === '' || $name === '' || $unit === '') {
            throw new InvalidArgumentException(
                'SKU, nama produk, dan satuan wajib diisi.'
            );
        }

        if ((float)($_POST['cost_price'] ?? 0) < 0) {
            throw new InvalidArgumentException(
                'Harga modal tidak boleh negatif.'
            );
        }

        if ((float)($_POST['sell_price'] ?? 0) < 0) {
            throw new InvalidArgumentException(
                'Harga jual tidak boleh negatif.'
            );
        }

        if ((float)($_POST['min_stock'] ?? 0) < 0) {
            throw new InvalidArgumentException(
                'Minimum stok tidak boleh negatif.'
            );
        }

        if (($_POST['action'] ?? '') === 'create'
            && (float)($_POST['stock'] ?? 0) < 0) {
            throw new InvalidArgumentException(
                'Stok awal tidak boleh negatif.'
            );
        }
    }

    public static function movement(): void
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            try {
                if (!empty($_POST['items']) && is_array($_POST['items'])) {
                    $type = ($_POST['type'] ?? 'IN') === 'IN' ? 'IN' : 'OUT';
                    $note = trim($_POST['note'] ?? '');
                    $defaultNote = $type === 'IN' ? 'Restock cepat via ringkasan mutasi' : 'Pemakaian stok via ringkasan mutasi';
                    $saved = 0;

                    foreach ($_POST['items'] as $pid => $qtyVal) {
                        $qty = (float)$qtyVal;
                        if ($qty > 0) {
                            StockModel::mutate(
                                (int)$pid,
                                $type,
                                $qty,
                                $note !== '' ? $note : $defaultNote,
                                user()['id']
                            );
                            $saved++;
                        }
                    }

                    if ($saved === 0) {
                        throw new InvalidArgumentException('Pilih minimal 1 produk dengan jumlah lebih dari 0.');
                    }

                    flash('success', "{$saved} item mutasi stok berhasil disimpan.");
                    $redirectTo = ($_POST['redirect_to'] ?? '') === 'dashboard' ? '/?page=dashboard' : '/?page=stock-movement';
                    redirect(BASE_URL . $redirectTo);
                }

                $productId = (int)$_POST['product_id'];
                $type = $_POST['type'] === 'IN' ? 'IN' : 'OUT';
                $qty = (float)$_POST['quantity'];
                $note = trim($_POST['note'] ?? '');

                StockModel::mutate(
                    $productId,
                    $type,
                    $qty,
                    $note,
                    user()['id']
                );

                flash('success', 'Mutasi stok berhasil disimpan.');
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }

            $redirectTo = ($_POST['redirect_to'] ?? '') === 'dashboard' ? '/?page=dashboard' : '/?page=stock-movement';
            redirect(BASE_URL . $redirectTo);
        }

        $products = StockModel::products();
        require __DIR__ . '/../views/stock_movement.php';
    }

    public static function history(): void
    {
        requireLogin();

        $orders = OrderModel::recentOrders(100);
        $movements = StockModel::movements(100);

        require __DIR__ . '/../views/stock_history.php';
    }
}
