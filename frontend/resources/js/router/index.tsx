import React from 'react';
import { createBrowserRouter } from 'react-router-dom';
import AppLayout from '../components/layout/AppLayout';
import AuthLayout from '../components/auth/AuthLayout';
import Home from '../pages/Home';
import Login from '../pages/auth/Login';
import Register from '../pages/auth/Register';
import ForgotPassword from '../pages/auth/ForgotPassword';
import ResetPassword from '../pages/auth/ResetPassword';
import ProductList from '../pages/products/ProductList';
import ProductDetail from '../pages/products/ProductDetail';
import Cart from '../pages/cart/Cart';
import Checkout from '../pages/checkout/Checkout';
import OrderConfirmation from '../pages/orders/OrderConfirmation';
import Profile from '../pages/profile/Profile';

const router = createBrowserRouter([
    {
        path: '/',
        element: <AppLayout />,
        children: [
            {
                index: true,
                element: <Home />
            },
            {
                path: 'products',
                element: <ProductList />
            },
            {
                path: 'product/:id',
                element: <ProductDetail />
            },
            {
                path: 'cart',
                element: <Cart />
            },
            {
                path: 'checkout',
                element: <Checkout />
            },
            {
                path: 'order-confirmation',
                element: <OrderConfirmation />
            },
            {
                path: 'profile',
                element: <Profile />
            }
        ]
    },
    {
        path: '/',
        element: <AuthLayout />,
        children: [
            {
                path: 'login',
                element: <Login />
            },
            {
                path: 'register',
                element: <Register />
            },
            {
                path: 'forgot-password',
                element: <ForgotPassword />
            },
            {
                path: 'reset-password',
                element: <ResetPassword />
            }
        ]
    }
]);

export default router; 