import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { FiCreditCard, FiDollarSign, FiSmartphone, FiArrowLeft } from 'react-icons/fi';

interface PaymentMethodProps {
    onSubmit: (data: PaymentInfo) => void;
    onBack: () => void;
    initialData: PaymentInfo | null;
}

interface PaymentInfo {
    method: 'card' | 'cod' | 'upi';
    cardNumber?: string;
    cardExpiry?: string;
    cardCvv?: string;
    upiId?: string;
}

const PaymentMethod: React.FC<PaymentMethodProps> = ({ onSubmit, onBack, initialData }) => {
    const [selectedMethod, setSelectedMethod] = useState<'card' | 'cod' | 'upi'>(
        initialData?.method || 'card'
    );

    const { register, handleSubmit, formState: { errors } } = useForm<PaymentInfo>({
        defaultValues: initialData || undefined
    });

    const paymentMethods = [
        {
            id: 'card',
            name: 'Credit/Debit Card',
            icon: FiCreditCard,
            description: 'Pay securely with your card'
        },
        {
            id: 'cod',
            name: 'Cash on Delivery',
            icon: FiDollarSign,
            description: 'Pay when you receive'
        },
        {
            id: 'upi',
            name: 'UPI',
            icon: FiSmartphone,
            description: 'Pay using UPI'
        }
    ];

    const handleFormSubmit = (data: PaymentInfo) => {
        onSubmit({
            ...data,
            method: selectedMethod
        });
    };

    return (
        <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-6">
            <div className="bg-white rounded-lg shadow-sm p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-6">Payment Method</h2>

                {/* Payment Method Selection */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    {paymentMethods.map((method) => {
                        const Icon = method.icon;
                        return (
                            <button
                                key={method.id}
                                type="button"
                                onClick={() => setSelectedMethod(method.id as 'card' | 'cod' | 'upi')}
                                className={`p-4 border rounded-lg text-left transition-colors ${
                                    selectedMethod === method.id
                                        ? 'border-primary bg-primary/5'
                                        : 'border-gray-200 hover:border-primary'
                                }`}
                            >
                                <Icon className="w-6 h-6 text-primary mb-2" />
                                <div className="font-medium text-gray-900">{method.name}</div>
                                <div className="text-sm text-gray-500">{method.description}</div>
                            </button>
                        );
                    })}
                </div>

                {/* Card Details */}
                {selectedMethod === 'card' && (
                    <div className="space-y-4">
                        <div>
                            <label htmlFor="cardNumber" className="block text-sm font-medium text-gray-700 mb-1">
                                Card Number *
                            </label>
                            <input
                                type="text"
                                id="cardNumber"
                                {...register('cardNumber', {
                                    required: 'Card number is required',
                                    pattern: {
                                        value: /^[0-9]{16}$/,
                                        message: 'Please enter a valid 16-digit card number'
                                    }
                                })}
                                placeholder="1234 5678 9012 3456"
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                            />
                            {errors.cardNumber && (
                                <p className="mt-1 text-sm text-red-600">{errors.cardNumber.message}</p>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="cardExpiry" className="block text-sm font-medium text-gray-700 mb-1">
                                    Expiry Date *
                                </label>
                                <input
                                    type="text"
                                    id="cardExpiry"
                                    {...register('cardExpiry', {
                                        required: 'Expiry date is required',
                                        pattern: {
                                            value: /^(0[1-9]|1[0-2])\/([0-9]{2})$/,
                                            message: 'Please enter a valid date (MM/YY)'
                                        }
                                    })}
                                    placeholder="MM/YY"
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                />
                                {errors.cardExpiry && (
                                    <p className="mt-1 text-sm text-red-600">{errors.cardExpiry.message}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="cardCvv" className="block text-sm font-medium text-gray-700 mb-1">
                                    CVV *
                                </label>
                                <input
                                    type="text"
                                    id="cardCvv"
                                    {...register('cardCvv', {
                                        required: 'CVV is required',
                                        pattern: {
                                            value: /^[0-9]{3,4}$/,
                                            message: 'Please enter a valid CVV'
                                        }
                                    })}
                                    placeholder="123"
                                    className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                />
                                {errors.cardCvv && (
                                    <p className="mt-1 text-sm text-red-600">{errors.cardCvv.message}</p>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* UPI Details */}
                {selectedMethod === 'upi' && (
                    <div>
                        <label htmlFor="upiId" className="block text-sm font-medium text-gray-700 mb-1">
                            UPI ID *
                        </label>
                        <input
                            type="text"
                            id="upiId"
                            {...register('upiId', {
                                required: 'UPI ID is required',
                                pattern: {
                                    value: /^[a-zA-Z0-9.-]{2,256}@[a-zA-Z][a-zA-Z]{2,64}$/,
                                    message: 'Please enter a valid UPI ID'
                                }
                            })}
                            placeholder="username@upi"
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                        {errors.upiId && (
                            <p className="mt-1 text-sm text-red-600">{errors.upiId.message}</p>
                        )}
                    </div>
                )}

                {/* COD Message */}
                {selectedMethod === 'cod' && (
                    <div className="bg-gray-50 p-4 rounded-md">
                        <p className="text-sm text-gray-600">
                            You will be required to pay the full amount when your order is delivered.
                            Our delivery partner will accept cash or card payment at the time of delivery.
                        </p>
                    </div>
                )}
            </div>

            <div className="flex justify-between">
                <button
                    type="button"
                    onClick={onBack}
                    className="flex items-center text-gray-600 hover:text-gray-900"
                >
                    <FiArrowLeft className="w-5 h-5 mr-2" />
                    Back to Shipping
                </button>

                <button
                    type="submit"
                    className="bg-primary text-white py-3 px-6 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50"
                >
                    Continue to Review
                </button>
            </div>
        </form>
    );
};

export default PaymentMethod; 