import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import AppLayout from './layout/AppLayout';
import AuthLayout from './auth/AuthLayout';
import AdminLayout from './admin/AdminLayout';
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
import Wishlist from '../pages/wishlist/Wishlist';
import SearchResults from '../pages/search/SearchResults';
import CategoryPage from '../pages/category/CategoryPage';

// Admin Pages
import AdminDashboard from '../pages/admin/Dashboard';
import AdminProducts from '../pages/admin/Products';
import AdminCategories from '../pages/admin/Categories';
import AdminOrders from '../pages/admin/Orders';
import AdminCustomers from '../pages/admin/Customers';
import AdminSettings from '../pages/admin/Settings';
import AdminLogin from '../pages/admin/Login';

const App: React.FC = () => {
    return (
        <Routes>
            {/* Admin Routes */}
            <Route path="admin">
                <Route path="login" element={<AdminLogin />} />
                <Route element={<AdminLayout />}>
                    <Route index element={<Navigate to="dashboard" replace />} />
                    <Route path="dashboard" element={<AdminDashboard />} />
                    <Route path="products" element={<AdminProducts />} />
                    <Route path="categories" element={<AdminCategories />} />
                    <Route path="orders" element={<AdminOrders />} />
                    <Route path="customers" element={<AdminCustomers />} />
                    <Route path="settings" element={<AdminSettings />} />
                </Route>
            </Route>

            {/* Auth Layout Routes */}
            <Route element={<AuthLayout />}>
                <Route path="login" element={<Login />} />
                <Route path="register" element={<Register />} />
                <Route path="forgot-password" element={<ForgotPassword />} />
                <Route path="reset-password" element={<ResetPassword />} />
            </Route>

            {/* Main Layout Routes */}
            <Route element={<AppLayout />}>
                <Route index element={<Home />} />
                <Route path="products" element={<ProductList />} />
                <Route path="product/:id" element={<ProductDetail />} />
                <Route path="cart" element={<Cart />} />
                <Route path="checkout" element={<Checkout />} />
                <Route path="order-confirmation" element={<OrderConfirmation />} />
                <Route path="profile/*" element={<Profile />} />
                <Route path="wishlist" element={<Wishlist />} />
                <Route path="search" element={<SearchResults />} />
                <Route path="category/:categoryId" element={<CategoryPage />} />
                <Route path="category/:categoryId/:subcategory" element={<CategoryPage />} />
            </Route>

            {/* Catch all route */}
            <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    );
};

export default App; 