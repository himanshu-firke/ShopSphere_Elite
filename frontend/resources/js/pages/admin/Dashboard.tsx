import React from 'react';
import { Link } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import {
    FiShoppingBag,
    FiUsers,
    FiDollarSign,
    FiPackage,
    FiTrendingUp,
    FiTrendingDown,
    FiAlertCircle
} from 'react-icons/fi';

// Mock data - replace with actual API data
const stats = [
    {
        name: 'Total Sales',
        value: '₹ 1,25,490',
        change: '+12.5%',
        isIncrease: true,
        icon: FiDollarSign
    },
    {
        name: 'Total Orders',
        value: '156',
        change: '+8.2%',
        isIncrease: true,
        icon: FiShoppingBag
    },
    {
        name: 'Total Customers',
        value: '2,450',
        change: '+15.3%',
        isIncrease: true,
        icon: FiUsers
    },
    {
        name: 'Products Sold',
        value: '384',
        change: '-2.4%',
        isIncrease: false,
        icon: FiPackage
    }
];

const recentOrders = [
    {
        id: 'ORD123456',
        customer: 'John Doe',
        date: '2024-02-20',
        amount: '₹ 12,490',
        status: 'completed'
    },
    {
        id: 'ORD123455',
        customer: 'Jane Smith',
        date: '2024-02-20',
        amount: '₹ 8,990',
        status: 'processing'
    },
    {
        id: 'ORD123454',
        customer: 'Mike Johnson',
        date: '2024-02-19',
        amount: '₹ 15,990',
        status: 'completed'
    },
    {
        id: 'ORD123453',
        customer: 'Sarah Williams',
        date: '2024-02-19',
        amount: '₹ 6,490',
        status: 'pending'
    }
];

const lowStockProducts = [
    {
        id: 1,
        name: 'Modern Office Chair',
        sku: 'CHAIR-001',
        stock: 3,
        minStock: 5
    },
    {
        id: 2,
        name: 'Executive Desk',
        sku: 'DESK-003',
        stock: 2,
        minStock: 5
    },
    {
        id: 3,
        name: 'Ergonomic Chair',
        sku: 'CHAIR-005',
        stock: 4,
        minStock: 5
    }
];

const AdminDashboard: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Dashboard - Admin Panel</title>
            </Helmet>

            <div className="space-y-6">
                {/* Page Header */}
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Overview of your store's performance and recent activities
                    </p>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {stats.map((stat) => (
                        <div
                            key={stat.name}
                            className="bg-white rounded-lg shadow-sm p-6"
                        >
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600">{stat.name}</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">{stat.value}</p>
                                </div>
                                <div className={`p-3 rounded-full ${
                                    stat.isIncrease ? 'bg-green-100' : 'bg-red-100'
                                }`}>
                                    <stat.icon className={`w-6 h-6 ${
                                        stat.isIncrease ? 'text-green-600' : 'text-red-600'
                                    }`} />
                                </div>
                            </div>
                            <div className="mt-4 flex items-center">
                                {stat.isIncrease ? (
                                    <FiTrendingUp className="w-4 h-4 text-green-600" />
                                ) : (
                                    <FiTrendingDown className="w-4 h-4 text-red-600" />
                                )}
                                <span className={`ml-2 text-sm ${
                                    stat.isIncrease ? 'text-green-600' : 'text-red-600'
                                }`}>
                                    {stat.change}
                                </span>
                                <span className="ml-2 text-sm text-gray-500">from last month</span>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Orders */}
                    <div className="bg-white rounded-lg shadow-sm">
                        <div className="p-6 border-b">
                            <div className="flex items-center justify-between">
                                <h2 className="text-lg font-medium text-gray-900">Recent Orders</h2>
                                <Link
                                    to="/admin/orders"
                                    className="text-sm font-medium text-primary hover:text-primary-dark"
                                >
                                    View all
                                </Link>
                            </div>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Order ID
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {recentOrders.map((order) => (
                                        <tr key={order.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {order.id}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {order.customer}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {order.date}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {order.amount}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
                                                    order.status === 'completed'
                                                        ? 'bg-green-100 text-green-800'
                                                        : order.status === 'processing'
                                                        ? 'bg-blue-100 text-blue-800'
                                                        : 'bg-yellow-100 text-yellow-800'
                                                }`}>
                                                    {order.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Low Stock Alert */}
                    <div className="bg-white rounded-lg shadow-sm">
                        <div className="p-6 border-b">
                            <div className="flex items-center justify-between">
                                <h2 className="text-lg font-medium text-gray-900">Low Stock Alert</h2>
                                <Link
                                    to="/admin/products"
                                    className="text-sm font-medium text-primary hover:text-primary-dark"
                                >
                                    View all products
                                </Link>
                            </div>
                        </div>
                        <div className="p-6">
                            <div className="space-y-4">
                                {lowStockProducts.map((product) => (
                                    <div
                                        key={product.id}
                                        className="flex items-center justify-between p-4 bg-red-50 rounded-lg"
                                    >
                                        <div className="flex items-center">
                                            <FiAlertCircle className="w-5 h-5 text-red-600" />
                                            <div className="ml-3">
                                                <p className="text-sm font-medium text-gray-900">
                                                    {product.name}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    SKU: {product.sku}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm font-medium text-gray-900">
                                                {product.stock} in stock
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                Min: {product.minStock}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default AdminDashboard; 