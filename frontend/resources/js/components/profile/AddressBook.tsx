import React, { useState } from 'react';
import { FiEdit2, FiTrash2, FiPlus } from 'react-icons/fi';

interface Address {
    id: number;
    firstName: string;
    lastName: string;
    phone: string;
    address: string;
    apartment?: string;
    city: string;
    state: string;
    pincode: string;
    isDefault: boolean;
}

// Mock data - replace with actual data from your backend
const mockAddresses: Address[] = [
    {
        id: 1,
        firstName: 'John',
        lastName: 'Doe',
        phone: '+91 9876543210',
        address: '123 Main Street',
        apartment: 'Apt 4B',
        city: 'Mumbai',
        state: 'Maharashtra',
        pincode: '400001',
        isDefault: true
    },
    {
        id: 2,
        firstName: 'John',
        lastName: 'Doe',
        phone: '+91 9876543210',
        address: '456 Work Avenue',
        city: 'Mumbai',
        state: 'Maharashtra',
        pincode: '400002',
        isDefault: false
    }
];

const AddressBook: React.FC = () => {
    const [addresses, setAddresses] = useState<Address[]>(mockAddresses);
    const [isAddingNew, setIsAddingNew] = useState(false);
    const [editingAddressId, setEditingAddressId] = useState<number | null>(null);

    const handleSetDefault = (addressId: number) => {
        setAddresses(addresses.map(addr => ({
            ...addr,
            isDefault: addr.id === addressId
        })));
    };

    const handleDelete = (addressId: number) => {
        if (window.confirm('Are you sure you want to delete this address?')) {
            setAddresses(addresses.filter(addr => addr.id !== addressId));
        }
    };

    const handleEdit = (addressId: number) => {
        setEditingAddressId(addressId);
        setIsAddingNew(false);
    };

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-lg font-medium text-gray-900">Saved Addresses</h2>
                <button
                    onClick={() => {
                        setIsAddingNew(true);
                        setEditingAddressId(null);
                    }}
                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                    <FiPlus className="-ml-1 mr-2 h-5 w-5" />
                    Add New Address
                </button>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {addresses.map(address => (
                    <div
                        key={address.id}
                        className={`relative p-4 border rounded-lg ${
                            address.isDefault ? 'border-primary bg-primary/5' : 'border-gray-200'
                        }`}
                    >
                        {address.isDefault && (
                            <span className="absolute top-4 right-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-white">
                                Default
                            </span>
                        )}

                        <div className="mt-4">
                            <h3 className="text-sm font-medium text-gray-900">
                                {address.firstName} {address.lastName}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                {address.address}
                                {address.apartment && <>, {address.apartment}</>}
                            </p>
                            <p className="text-sm text-gray-500">
                                {address.city}, {address.state} {address.pincode}
                            </p>
                            <p className="mt-2 text-sm text-gray-500">
                                Phone: {address.phone}
                            </p>
                        </div>

                        <div className="mt-4 flex items-center justify-end space-x-4">
                            {!address.isDefault && (
                                <button
                                    onClick={() => handleSetDefault(address.id)}
                                    className="text-sm text-primary hover:text-primary-dark"
                                >
                                    Set as Default
                                </button>
                            )}
                            <button
                                onClick={() => handleEdit(address.id)}
                                className="text-gray-400 hover:text-gray-500"
                            >
                                <FiEdit2 className="h-5 w-5" />
                            </button>
                            {!address.isDefault && (
                                <button
                                    onClick={() => handleDelete(address.id)}
                                    className="text-gray-400 hover:text-gray-500"
                                >
                                    <FiTrash2 className="h-5 w-5" />
                                </button>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {/* Add/Edit Address Form would go here */}
            {(isAddingNew || editingAddressId !== null) && (
                <div className="mt-6 border-t border-gray-200 pt-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                        {isAddingNew ? 'Add New Address' : 'Edit Address'}
                    </h3>
                    {/* Form component would go here */}
                </div>
            )}
        </div>
    );
};

export default AddressBook; 