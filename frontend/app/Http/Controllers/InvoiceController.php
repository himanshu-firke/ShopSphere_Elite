namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Generate invoice for an order
     */
    public function generate(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Verify order belongs to authenticated user if not guest
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        try {
            $path = $this->invoiceService->generateInvoice($order);

            return response()->json([
                'success' => true,
                'invoice_url' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate invoice',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download invoice
     */
    public function download(string $orderNumber): Response
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Verify order belongs to authenticated user if not guest
        if (auth()->check() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $path = 'invoices/invoice_' . $order->order_number . '.pdf';

        if (!Storage::exists($path)) {
            // Generate invoice if it doesn't exist
            $path = $this->invoiceService->generateInvoice($order);
        }

        return Storage::download($path, 'Invoice_' . $order->order_number . '.pdf', [
            'Content-Type' => 'application/pdf'
        ]);
    }
} 