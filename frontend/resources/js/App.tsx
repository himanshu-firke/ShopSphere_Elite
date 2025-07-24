import React from 'react';
import { Helmet } from 'react-helmet';
import { HelmetProvider } from 'react-helmet-async';
import { RouterProvider } from 'react-router-dom';
import router from './router';

const App: React.FC = () => {
    return (
        <HelmetProvider>
            <Helmet>
                <title>EcommerceKanha - India's Favourite Furniture Store</title>
                <meta name="description" content="Shop from our wide range of furniture including living room, bedroom, dining room, and more. Best prices and quality guaranteed." />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <meta name="theme-color" content="#ffffff" />
            </Helmet>
            <RouterProvider router={router} />
        </HelmetProvider>
    );
};

export default App; 