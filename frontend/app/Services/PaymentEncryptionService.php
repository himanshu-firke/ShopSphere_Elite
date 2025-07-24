namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PaymentEncryptionService
{
    /**
     * Encrypt sensitive payment data
     */
    public function encryptPaymentData(array $data): array
    {
        $encryptedData = [];
        $encryptionKey = $this->generateEncryptionKey();

        foreach ($data as $key => $value) {
            if ($this->shouldEncrypt($key)) {
                $encryptedData[$key] = [
                    'value' => Crypt::encryptString($value),
                    'key' => $encryptionKey
                ];
            } else {
                $encryptedData[$key] = $value;
            }
        }

        return $encryptedData;
    }

    /**
     * Decrypt sensitive payment data
     */
    public function decryptPaymentData(array $data): array
    {
        $decryptedData = [];

        foreach ($data as $key => $value) {
            if ($this->shouldEncrypt($key) && is_array($value)) {
                try {
                    $decryptedData[$key] = Crypt::decryptString($value['value']);
                } catch (\Exception $e) {
                    \Log::error('Failed to decrypt payment data', [
                        'key' => $key,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            } else {
                $decryptedData[$key] = $value;
            }
        }

        return $decryptedData;
    }

    /**
     * Generate a unique encryption key
     */
    protected function generateEncryptionKey(): string
    {
        return Str::random(32);
    }

    /**
     * Check if a field should be encrypted
     */
    protected function shouldEncrypt(string $field): bool
    {
        return in_array($field, [
            'card_number',
            'cvv',
            'expiry_month',
            'expiry_year',
            'bank_account_number',
            'routing_number',
            'payment_token'
        ]);
    }

    /**
     * Mask sensitive data for logging
     */
    public function maskSensitiveData(array $data): array
    {
        $maskedData = [];

        foreach ($data as $key => $value) {
            if ($this->shouldEncrypt($key)) {
                $maskedData[$key] = $this->maskValue($value, $key);
            } else {
                $maskedData[$key] = $value;
            }
        }

        return $maskedData;
    }

    /**
     * Mask a specific value based on its type
     */
    protected function maskValue(string $value, string $field): string
    {
        switch ($field) {
            case 'card_number':
                return str_repeat('*', strlen($value) - 4) . substr($value, -4);
            case 'cvv':
                return str_repeat('*', strlen($value));
            case 'expiry_month':
            case 'expiry_year':
                return $value; // These are not sensitive enough to mask
            case 'bank_account_number':
                return str_repeat('*', strlen($value) - 4) . substr($value, -4);
            case 'routing_number':
                return str_repeat('*', strlen($value) - 2) . substr($value, -2);
            default:
                return str_repeat('*', strlen($value));
        }
    }
} 