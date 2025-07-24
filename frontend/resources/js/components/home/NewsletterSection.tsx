import React, { useState } from 'react';
import { FiMail, FiFacebook, FiInstagram, FiTwitter, FiYoutube } from 'react-icons/fi';

const NewsletterSection: React.FC = () => {
    const [email, setEmail] = useState('');
    const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
    const [message, setMessage] = useState('');

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setStatus('loading');

        // TODO: Implement actual newsletter subscription
        setTimeout(() => {
            setStatus('success');
            setMessage('Thank you for subscribing! Check your email for confirmation.');
            setEmail('');
        }, 1000);
    };

    return (
        <section className="bg-primary py-16">
            <div className="container mx-auto px-4">
                <div className="max-w-4xl mx-auto">
                    {/* Newsletter Subscription */}
                    <div className="text-center mb-12">
                        <div className="flex justify-center mb-6">
                            <div className="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                                <FiMail className="w-8 h-8 text-white" />
                            </div>
                        </div>
                        <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">
                            Stay Updated with Latest Trends
                        </h2>
                        <p className="text-white/80 max-w-2xl mx-auto mb-8">
                            Subscribe to our newsletter and get exclusive offers, interior design tips,
                            and new arrival updates straight to your inbox
                        </p>

                        <form onSubmit={handleSubmit} className="max-w-md mx-auto">
                            <div className="flex gap-4">
                                <input
                                    type="email"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    placeholder="Enter your email address"
                                    className="flex-1 px-6 py-3 rounded-full focus:outline-none focus:ring-2 focus:ring-white/20"
                                    required
                                />
                                <button
                                    type="submit"
                                    disabled={status === 'loading'}
                                    className="bg-white text-primary px-8 py-3 rounded-full font-semibold hover:bg-white/90 transition-colors disabled:opacity-70"
                                >
                                    {status === 'loading' ? 'Subscribing...' : 'Subscribe'}
                                </button>
                            </div>
                            {message && (
                                <p
                                    className={`mt-4 text-sm ${
                                        status === 'success' ? 'text-green-300' : 'text-red-300'
                                    }`}
                                >
                                    {message}
                                </p>
                            )}
                        </form>
                    </div>

                    {/* Social Links */}
                    <div className="border-t border-white/10 pt-12">
                        <div className="text-center">
                            <h3 className="text-xl font-semibold text-white mb-6">
                                Connect With Us
                            </h3>
                            <div className="flex justify-center space-x-6">
                                <a
                                    href="https://facebook.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                                >
                                    <FiFacebook className="w-6 h-6 text-white" />
                                </a>
                                <a
                                    href="https://instagram.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                                >
                                    <FiInstagram className="w-6 h-6 text-white" />
                                </a>
                                <a
                                    href="https://twitter.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                                >
                                    <FiTwitter className="w-6 h-6 text-white" />
                                </a>
                                <a
                                    href="https://youtube.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                                >
                                    <FiYoutube className="w-6 h-6 text-white" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default NewsletterSection; 