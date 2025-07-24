import React from 'react';
import { Helmet } from 'react-helmet-async';

const AdminCategories: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Categories - Admin Panel</title>
            </Helmet>

            <div>
                <h1 className="text-2xl font-bold text-gray-900">Categories</h1>
                <p className="mt-1 text-sm text-gray-500">
                    Manage your product categories
                </p>
            </div>
        </>
    );
};

export default AdminCategories; 