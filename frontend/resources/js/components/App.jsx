import React from 'react';
import { Routes, Route } from 'react-router-dom';
import { HelmetProvider } from 'react-helmet-async';
import AppLayout from './layout/AppLayout';
import AuthLayout from './auth/AuthLayout';
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

function App() {
    return (
        <HelmetProvider>
            <Routes>
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
                </Route>

                {/* Auth Layout Routes */}
                <Route element={<AuthLayout />}>
                    <Route path="login" element={<Login />} />
                    <Route path="register" element={<Register />} />
                    <Route path="forgot-password" element={<ForgotPassword />} />
                    <Route path="reset-password" element={<ResetPassword />} />
                </Route>
            </Routes>
        </HelmetProvider>
    );
}

export default App;