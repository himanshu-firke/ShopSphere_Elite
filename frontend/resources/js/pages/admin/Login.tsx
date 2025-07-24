import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import PageTitle from '../../components/common/PageTitle';

interface LoginForm {
    email: string;
    password: string;
}

const AdminLogin: React.FC = () => {
    const navigate = useNavigate();
    const { register, handleSubmit, formState: { errors }, setError } = useForm<LoginForm>();

    const onSubmit = async (data: LoginForm) => {
        try {
            // Mock login - replace with actual API call
            if (data.email === 'admin@example.com' && data.password === 'admin123') {
                // Set auth token
                sessionStorage.setItem('adminAuth', 'true');
                
                // Check for redirect URL
                const redirectUrl = sessionStorage.getItem('adminRedirectUrl') || '/admin/dashboard';
                sessionStorage.removeItem('adminRedirectUrl');
                
                navigate(redirectUrl);
            } else {
                setError('email', {
                    type: 'manual',
                    message: 'Invalid email or password'
                });
            }
        } catch (error) {
            console.error('Login failed:', error);
            setError('email', {
                type: 'manual',
                message: 'An error occurred. Please try again.'
            });
        }
    };

    // If already authenticated, redirect to dashboard
    React.useEffect(() => {
        if (sessionStorage.getItem('adminAuth') === 'true') {
            navigate('/admin/dashboard');
        }
    }, [navigate]);

    return (
        <>
            <PageTitle title="Admin Login" />

            <div className="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <h1 className="text-center text-3xl font-bold text-gray-900">
                        Kanha Furniture
                    </h1>
                    <h2 className="mt-2 text-center text-lg text-gray-600">
                        Admin Dashboard
                    </h2>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="bg-white py-8 px-4 shadow-sm rounded-lg sm:px-10">
                        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                            <div>
                                <label
                                    htmlFor="email"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Email address
                                </label>
                                <div className="mt-1">
                                    <input
                                        id="email"
                                        type="email"
                                        autoComplete="email"
                                        {...register('email', {
                                            required: 'Email is required',
                                            pattern: {
                                                value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                                                message: 'Invalid email address'
                                            }
                                        })}
                                        className={`appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm ${
                                            errors.email ? 'border-red-300' : 'border-gray-300'
                                        }`}
                                    />
                                    {errors.email && (
                                        <p className="mt-2 text-sm text-red-600">
                                            {errors.email.message}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label
                                    htmlFor="password"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Password
                                </label>
                                <div className="mt-1">
                                    <input
                                        id="password"
                                        type="password"
                                        autoComplete="current-password"
                                        {...register('password', {
                                            required: 'Password is required',
                                            minLength: {
                                                value: 6,
                                                message: 'Password must be at least 6 characters'
                                            }
                                        })}
                                        className={`appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm ${
                                            errors.password ? 'border-red-300' : 'border-gray-300'
                                        }`}
                                    />
                                    {errors.password && (
                                        <p className="mt-2 text-sm text-red-600">
                                            {errors.password.message}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center justify-between">
                                <div className="flex items-center">
                                    <input
                                        id="remember-me"
                                        name="remember-me"
                                        type="checkbox"
                                        className="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                                    />
                                    <label
                                        htmlFor="remember-me"
                                        className="ml-2 block text-sm text-gray-900"
                                    >
                                        Remember me
                                    </label>
                                </div>

                                <div className="text-sm">
                                    <a
                                        href="#"
                                        className="font-medium text-primary hover:text-primary-dark"
                                    >
                                        Forgot your password?
                                    </a>
                                </div>
                            </div>

                            <div>
                                <button
                                    type="submit"
                                    className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                >
                                    Sign in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
};

export default AdminLogin; 