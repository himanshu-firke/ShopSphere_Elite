namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate invoice for an order
     */
    public function generateInvoice(Order $order): string
    {
        $pdf = PDF::loadView('invoices.template', [
            'order' => $order->load(['items', 'shippingAddress', 'billingAddress', 'user']),
            'company' => [
                'name' => config('app.name'),
                'address' => config('company.address'),
                'phone' => config('company.phone'),
                'email' => config('company.email'),
                'gst' => config('company.gst'),
            ]
        ]);

        $filename = 'invoice_' . $order->order_number . '.pdf';
        $path = 'invoices/' . $filename;

        // Store the PDF
        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Get invoice file URL
     */
    public function getInvoiceUrl(Order $order): ?string
    {
        $path = 'invoices/invoice_' . $order->order_number . '.pdf';

        if (!Storage::exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    /**
     * Delete invoice file
     */
    public function deleteInvoice(Order $order): bool
    {
        $path = 'invoices/invoice_' . $order->order_number . '.pdf';

        if (Storage::exists($path)) {
            return Storage::delete($path);
        }

        return false;
    }
} 