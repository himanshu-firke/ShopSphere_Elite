import React from 'react';
import { Helmet } from 'react-helmet-async';

const AdminOrders: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Orders - Admin Panel</title>
            </Helmet>

            <div>
                <h1 className="text-2xl font-bold text-gray-900">Orders</h1>
                <p className="mt-1 text-sm text-gray-500">
                    Manage customer orders
                </p>
            </div>
        </>
    );
};

export default AdminOrders; 