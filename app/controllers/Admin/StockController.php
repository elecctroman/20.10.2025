<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Models\StockCode;
use App\Models\Variant;

class StockController extends Controller
{
    public function index()
    {
        Auth::requireAdmin();

        $filters = [
            'product_id' => $_GET['product_id'] ?? null,
            'variant_id' => $_GET['variant_id'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        $variantModel = new Variant();
        $stockModel = new StockCode();

        $products = database()->query('SELECT id, name FROM products ORDER BY name')->fetchAll();
        $variants = [];
        if (!empty($filters['product_id'])) {
            $variants = $variantModel->byProduct((int) $filters['product_id']);
        }

        $codes = $stockModel->list($filters);

        return $this->view('admin/stock_pool', [
            'products' => $products,
            'variants' => $variants,
            'codes' => $codes,
            'filters' => $filters,
        ], 'admin');
    }

    public function upload()
    {
        Auth::requireAdmin();

        if (!CSRF::verify($_POST['_token'] ?? '')) {
            session_flash('error', 'Oturum doğrulanamadı.');
            return $this->redirect('/admin/stock');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = $_POST['variant_id'] !== '' ? (int) $_POST['variant_id'] : null;
        $variantModel = new Variant();
        if ($variantId) {
            $variant = $variantModel->find($variantId);
            if (!$variant || (int) $variant['product_id'] !== $productId) {
                session_flash('error', 'Varyant seçimi ürün ile eşleşmiyor.');
                return $this->redirect('/admin/stock');
            }
        }

        if ($productId <= 0) {
            session_flash('error', 'Lütfen bir ürün seçiniz.');
            return $this->redirect('/admin/stock');
        }

        $codes = [];
        if (!empty($_POST['bulk_codes'])) {
            $codes = array_merge($codes, preg_split('/\r?\n/', trim($_POST['bulk_codes'])));
        }

        if (!empty($_FILES['csv_file']['tmp_name']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            $mime = mime_content_type($_FILES['csv_file']['tmp_name']);
            if (!in_array($mime, ['text/plain', 'text/csv', 'application/vnd.ms-excel'], true)) {
                session_flash('error', 'CSV/TXT formatı dışında dosya yüklenemez.');
                return $this->redirect('/admin/stock');
            }
            $fileContent = file_get_contents($_FILES['csv_file']['tmp_name']);
            $codes = array_merge($codes, preg_split('/\r?\n/', $fileContent));
        }

        $codes = array_filter(array_map('trim', $codes), fn ($code) => $code !== '');

        if (empty($codes)) {
            session_flash('error', 'Yüklenecek kod bulunamadı.');
            return $this->redirect('/admin/stock');
        }

        $stockModel = new StockCode();
        $inserted = $stockModel->createMany($productId, $variantId, $codes);

        session_flash('success', $inserted . ' adet kod stok havuzuna eklendi.');
        audit('stock_upload', ['product_id' => $productId, 'variant_id' => $variantId, 'count' => $inserted]);

        return $this->redirect('/admin/stock');
    }
}
