import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { FiChevronRight } from 'react-icons/fi';
import { useCart } from '../../contexts/CartContext';
import ShippingForm from '../../components/checkout/ShippingForm';
import PaymentMethod from '../../components/checkout/PaymentMethod';
import OrderReview from '../../components/checkout/OrderReview';

type CheckoutStep = 'shipping' | 'payment' | 'review';

interface ShippingInfo {
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    address: string;
    apartment?: string;
    city: string;
    state: string;
    pincode: string;
}

interface PaymentInfo {
    method: 'card' | 'cod' | 'upi';
    cardNumber?: string;
    cardExpiry?: string;
    cardCvv?: string;
    upiId?: string;
}

const Checkout: React.FC = () => {
    const navigate = useNavigate();
    const { state: cartState } = useCart();
    const [currentStep, setCurrentStep] = useState<CheckoutStep>('shipping');
    const [shippingInfo, setShippingInfo] = useState<ShippingInfo | null>(null);
    const [paymentInfo, setPaymentInfo] = useState<PaymentInfo | null>(null);

    // Redirect to cart if cart is empty
    if (cartState.items.length === 0) {
        navigate('/cart');
        return null;
    }

    const steps = [
        { id: 'shipping', label: 'Shipping' },
        { id: 'payment', label: 'Payment' },
        { id: 'review', label: 'Review' }
    ];

    const handleShippingSubmit = (data: ShippingInfo) => {
        setShippingInfo(data);
        setCurrentStep('payment');
    };

    const handlePaymentSubmit = (data: PaymentInfo) => {
        setPaymentInfo(data);
        setCurrentStep('review');
    };

    const handlePlaceOrder = async () => {
        // Here you would typically:
        // 1. Validate all information
        // 2. Send order to backend
        // 3. Process payment
        // 4. Show confirmation
        
        // For now, we'll just simulate a successful order
        navigate('/order-confirmation', {
            state: {
                orderNumber: 'ORD' + Date.now(),
                shippingInfo,
                paymentInfo,
                orderItems: cartState.items,
                orderTotal: cartState.total
            }
        });
    };

    return (
        <>
            <Helmet>
                <title>Checkout - Kanha Furniture</title>
                <meta name="description" content="Complete your order at Kanha Furniture" />
            </Helmet>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Checkout Steps */}
                <div className="mb-8">
                    <div className="flex items-center justify-center">
                        {steps.map((step, index) => (
                            <React.Fragment key={step.id}>
                                <div className="flex items-center">
                                    <div
                                        className={`w-8 h-8 rounded-full flex items-center justify-center ${
                                            currentStep === step.id
                                                ? 'bg-primary text-white'
                                                : steps.indexOf({ id: currentStep, label: '' }) > index
                                                ? 'bg-green-500 text-white'
                                                : 'bg-gray-200 text-gray-600'
                                        }`}
                                    >
                                        {steps.indexOf({ id: currentStep, label: '' }) > index ? '✓' : index + 1}
                                    </div>
                                    <span
                                        className={`ml-2 ${
                                            currentStep === step.id ? 'text-primary font-medium' : 'text-gray-500'
                                        }`}
                                    >
                                        {step.label}
                                    </span>
                                </div>
                                {index < steps.length - 1 && (
                                    <div className="w-16 sm:w-24 border-t border-gray-200 mx-4" />
                                )}
                            </React.Fragment>
                        ))}
                    </div>
                </div>

                {/* Step Content */}
                <div className="max-w-3xl mx-auto">
                    {currentStep === 'shipping' && (
                        <ShippingForm
                            onSubmit={handleShippingSubmit}
                            initialData={shippingInfo}
                        />
                    )}

                    {currentStep === 'payment' && (
                        <PaymentMethod
                            onSubmit={handlePaymentSubmit}
                            onBack={() => setCurrentStep('shipping')}
                            initialData={paymentInfo}
                        />
                    )}

                    {currentStep === 'review' && (
                        <OrderReview
                            shippingInfo={shippingInfo!}
                            paymentInfo={paymentInfo!}
                            cartItems={cartState.items}
                            subtotal={cartState.subtotal}
                            shipping={cartState.shipping}
                            discount={cartState.discount}
                            total={cartState.total}
                            onBack={() => setCurrentStep('payment')}
                            onPlaceOrder={handlePlaceOrder}
                        />
                    )}
                </div>
            </div>
        </>
    );
};

export default Checkout; 