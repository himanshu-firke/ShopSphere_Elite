import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { CartProvider } from './contexts/CartContext';
import App from './components/App';

const container = document.getElementById('root');
if (!container) {
    throw new Error('Root element not found! Make sure there is a div with id="root" in your HTML');
}

const root = createRoot(container);

root.render(
    <React.StrictMode>
        <BrowserRouter>
            <CartProvider>
                <App />
            </CartProvider>
        </BrowserRouter>
    </React.StrictMode>
); 