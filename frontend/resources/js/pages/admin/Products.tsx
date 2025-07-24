import React from 'react';
import { Helmet } from 'react-helmet-async';

const AdminProducts: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Products - Admin Panel</title>
            </Helmet>

            <div>
                <h1 className="text-2xl font-bold text-gray-900">Products</h1>
                <p className="mt-1 text-sm text-gray-500">
                    Manage your store's products
                </p>
            </div>
        </>
    );
};

export default AdminProducts; 