import React from 'react';
import { Helmet } from 'react-helmet-async';

const AdminSettings: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Settings - Admin Panel</title>
            </Helmet>

            <div>
                <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
                <p className="mt-1 text-sm text-gray-500">
                    Configure store settings
                </p>
            </div>
        </>
    );
};

export default AdminSettings; 