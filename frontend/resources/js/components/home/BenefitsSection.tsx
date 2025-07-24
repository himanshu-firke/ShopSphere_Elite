import React from 'react';
import { FiTruck, FiCreditCard, FiTool, FiShield } from 'react-icons/fi';

interface Benefit {
    icon: React.ReactNode;
    title: string;
    description: string;
}

const benefits: Benefit[] = [
    {
        icon: <FiTruck className="w-8 h-8" />,
        title: 'Free Shipping & Assembly',
        description: 'Free delivery and professional assembly across major cities'
    },
    {
        icon: <FiCreditCard className="w-8 h-8" />,
        title: 'Easy EMI Options',
        description: 'No-cost EMI available on all major credit cards'
    },
    {
        icon: <FiShield className="w-8 h-8" />,
        title: '3-Year Warranty',
        description: 'Extended warranty with quality assurance guarantee'
    },
    {
        icon: <FiTool className="w-8 h-8" />,
        title: 'After-Sale Service',
        description: 'Dedicated support team for maintenance and repairs'
    }
];

const BenefitsSection: React.FC = () => {
    return (
        <section className="bg-gray-50 py-16">
            <div className="container mx-auto px-4">
                <div className="text-center mb-12">
                    <h2 className="text-3xl md:text-4xl font-bold mb-4">Why Choose Us</h2>
                    <p className="text-gray-600 max-w-2xl mx-auto">
                        We offer the best furniture shopping experience with these amazing benefits
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    {benefits.map((benefit, index) => (
                        <div
                            key={index}
                            className="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow"
                        >
                            <div className="flex flex-col items-center text-center">
                                <div className="w-16 h-16 flex items-center justify-center bg-primary/10 text-primary rounded-full mb-4">
                                    {benefit.icon}
                                </div>
                                <h3 className="text-xl font-semibold mb-2">{benefit.title}</h3>
                                <p className="text-gray-600">{benefit.description}</p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Trust Badges */}
                <div className="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div className="flex flex-col items-center">
                        <span className="text-3xl font-bold text-primary mb-2">50K+</span>
                        <span className="text-gray-600">Happy Customers</span>
                    </div>
                    <div className="flex flex-col items-center">
                        <span className="text-3xl font-bold text-primary mb-2">1000+</span>
                        <span className="text-gray-600">Products</span>
                    </div>
                    <div className="flex flex-col items-center">
                        <span className="text-3xl font-bold text-primary mb-2">20+</span>
                        <span className="text-gray-600">Cities Covered</span>
                    </div>
                    <div className="flex flex-col items-center">
                        <span className="text-3xl font-bold text-primary mb-2">4.8/5</span>
                        <span className="text-gray-600">Customer Rating</span>
                    </div>
                </div>

                {/* Payment Partners */}
                <div className="mt-16">
                    <div className="text-center mb-8">
                        <h3 className="text-xl font-semibold">Trusted Payment Partners</h3>
                    </div>
                    <div className="flex justify-center items-center space-x-8">
                        <img
                            src="/images/payment/visa.png"
                            alt="Visa"
                            className="h-8 grayscale hover:grayscale-0 transition-all"
                        />
                        <img
                            src="/images/payment/mastercard.png"
                            alt="Mastercard"
                            className="h-8 grayscale hover:grayscale-0 transition-all"
                        />
                        {/* Add more payment partners as needed */}
                    </div>
                </div>
            </div>
        </section>
    );
};

export default BenefitsSection; 