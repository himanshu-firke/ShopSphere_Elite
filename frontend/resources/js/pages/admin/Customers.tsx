import React from 'react';
import { Helmet } from 'react-helmet-async';

const AdminCustomers: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Customers - Admin Panel</title>
            </Helmet>

            <div>
                <h1 className="text-2xl font-bold text-gray-900">Customers</h1>
                <p className="mt-1 text-sm text-gray-500">
                    Manage customer accounts
                </p>
            </div>
        </>
    );
};

export default AdminCustomers; 