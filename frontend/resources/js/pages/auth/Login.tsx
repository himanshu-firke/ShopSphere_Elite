import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import AuthLayout from '../../components/auth/AuthLayout';

const Login: React.FC = () => {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        email: '',
        password: ''
    });
    const [errors, setErrors] = useState<Record<string, string>>({});

    const validateForm = () => {
        const newErrors: Record<string, string> = {};
        
        if (!formData.email) {
            newErrors.email = 'Email is required';
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = 'Please enter a valid email address';
        }
        
        if (!formData.password) {
            newErrors.password = 'Password is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (validateForm()) {
            try {
                // TODO: Implement actual login API call
                console.log('Login attempt with:', formData);
                navigate('/dashboard');
            } catch (error) {
                console.error('Login failed:', error);
                setErrors({ submit: 'Login failed. Please try again.' });
            }
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    return (
        <AuthLayout>
            <div>
                <h2 className="text-center text-2xl font-bold bg-yellow-100 py-2 mb-6">
                    CUSTOMER LOGIN
                </h2>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <input
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            placeholder="Email address"
                            className={`appearance-none block w-full px-3 py-2 border ${
                                errors.email ? 'border-red-300' : 'border-gray-300'
                            } rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary`}
                        />
                        {errors.email && (
                            <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                        )}
                    </div>

                    <div>
                        <input
                            type="password"
                            name="password"
                            value={formData.password}
                            onChange={handleChange}
                            placeholder="Password"
                            className={`appearance-none block w-full px-3 py-2 border ${
                                errors.password ? 'border-red-300' : 'border-gray-300'
                            } rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary`}
                        />
                        {errors.password && (
                            <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                        )}
                    </div>

                    <div className="text-center">
                        <Link
                            to="/forgot-password"
                            className="text-sm text-primary hover:text-primary-dark"
                        >
                            Forgot your password?
                        </Link>
                    </div>

                    {errors.submit && (
                        <div className="text-center text-red-600 text-sm">
                            {errors.submit}
                        </div>
                    )}

                    <button
                        type="submit"
                        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        Login
                    </button>

                    <div className="text-center mt-4">
                        <span className="text-sm text-gray-600">Don't have an account? </span>
                        <Link
                            to="/register"
                            className="text-sm text-primary hover:text-primary-dark font-medium"
                        >
                            Register Now
                        </Link>
                    </div>
                </form>
            </div>
        </AuthLayout>
    );
};

export default Login; 