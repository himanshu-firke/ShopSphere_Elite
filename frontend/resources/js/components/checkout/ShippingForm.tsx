import React from 'react';
import { useForm } from 'react-hook-form';

interface ShippingFormProps {
    onSubmit: (data: ShippingInfo) => void;
    initialData: ShippingInfo | null;
}

interface ShippingInfo {
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    address: string;
    apartment?: string;
    city: string;
    state: string;
    pincode: string;
}

const ShippingForm: React.FC<ShippingFormProps> = ({ onSubmit, initialData }) => {
    const { register, handleSubmit, formState: { errors } } = useForm<ShippingInfo>({
        defaultValues: initialData || undefined
    });

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            <div className="bg-white rounded-lg shadow-sm p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-6">Shipping Information</h2>

                {/* Name Fields */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label htmlFor="firstName" className="block text-sm font-medium text-gray-700 mb-1">
                            First Name *
                        </label>
                        <input
                            type="text"
                            id="firstName"
                            {...register('firstName', { required: 'First name is required' })}
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                        {errors.firstName && (
                            <p className="mt-1 text-sm text-red-600">{errors.firstName.message}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="lastName" className="block text-sm font-medium text-gray-700 mb-1">
                            Last Name *
                        </label>
                        <input
                            type="text"
                            id="lastName"
                            {...register('lastName', { required: 'Last name is required' })}
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                        {errors.lastName && (
                            <p className="mt-1 text-sm text-red-600">{errors.lastName.message}</p>
                        )}
                    </div>
                </div>

                {/* Contact Fields */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                            Email *
                        </label>
                        <input
                            type="email"
                            id="email"
                            {...register('email', {
                                required: 'Email is required',
                                pattern: {
                                    value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                                    message: 'Invalid email address'
                                }
                            })}
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                        {errors.email && (
                            <p className="mt-1 text-sm text-red-600">{errors.email.message}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-1">
                            Phone *
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            {...register('phone', {
                                required: 'Phone number is required',
                                pattern: {
                                    value: /^[0-9]{10}$/,
                                    message: 'Please enter a valid 10-digit phone number'
                                }
                            })}
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                        {errors.phone && (
                            <p className="mt-1 text-sm text-red-600">{errors.phone.message}</p>
                        )}
                    </div>
                </div>

                {/* Address Fields */}
                <div className="space-y-4">
                    <div>
                        <label htmlFor="address" className="block text-sm font-medium text-gray-700 mb-1">
                            Address *
                        </label>
                        <input
                            type="text"
                            id="address"
                            {...register('address', { required: 'Address is required' })}
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                        {errors.address && (
                            <p className="mt-1 text-sm text-red-600">{errors.address.message}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="apartment" className="block text-sm font-medium text-gray-700 mb-1">
                            Apartment, suite, etc. (optional)
                        </label>
                        <input
                            type="text"
                            id="apartment"
                            {...register('apartment')}
                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label htmlFor="city" className="block text-sm font-medium text-gray-700 mb-1">
                                City *
                            </label>
                            <input
                                type="text"
                                id="city"
                                {...register('city', { required: 'City is required' })}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                            />
                            {errors.city && (
                                <p className="mt-1 text-sm text-red-600">{errors.city.message}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="state" className="block text-sm font-medium text-gray-700 mb-1">
                                State *
                            </label>
                            <select
                                id="state"
                                {...register('state', { required: 'State is required' })}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                            >
                                <option value="">Select State</option>
                                <option value="AN">Andaman and Nicobar Islands</option>
                                <option value="AP">Andhra Pradesh</option>
                                <option value="AR">Arunachal Pradesh</option>
                                <option value="AS">Assam</option>
                                <option value="BR">Bihar</option>
                                <option value="CH">Chandigarh</option>
                                <option value="CT">Chhattisgarh</option>
                                <option value="DN">Dadra and Nagar Haveli</option>
                                <option value="DD">Daman and Diu</option>
                                <option value="DL">Delhi</option>
                                <option value="GA">Goa</option>
                                <option value="GJ">Gujarat</option>
                                <option value="HR">Haryana</option>
                                <option value="HP">Himachal Pradesh</option>
                                <option value="JK">Jammu and Kashmir</option>
                                <option value="JH">Jharkhand</option>
                                <option value="KA">Karnataka</option>
                                <option value="KL">Kerala</option>
                                <option value="LA">Ladakh</option>
                                <option value="LD">Lakshadweep</option>
                                <option value="MP">Madhya Pradesh</option>
                                <option value="MH">Maharashtra</option>
                                <option value="MN">Manipur</option>
                                <option value="ML">Meghalaya</option>
                                <option value="MZ">Mizoram</option>
                                <option value="NL">Nagaland</option>
                                <option value="OR">Odisha</option>
                                <option value="PY">Puducherry</option>
                                <option value="PB">Punjab</option>
                                <option value="RJ">Rajasthan</option>
                                <option value="SK">Sikkim</option>
                                <option value="TN">Tamil Nadu</option>
                                <option value="TG">Telangana</option>
                                <option value="TR">Tripura</option>
                                <option value="UP">Uttar Pradesh</option>
                                <option value="UT">Uttarakhand</option>
                                <option value="WB">West Bengal</option>
                            </select>
                            {errors.state && (
                                <p className="mt-1 text-sm text-red-600">{errors.state.message}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="pincode" className="block text-sm font-medium text-gray-700 mb-1">
                                PIN Code *
                            </label>
                            <input
                                type="text"
                                id="pincode"
                                {...register('pincode', {
                                    required: 'PIN code is required',
                                    pattern: {
                                        value: /^[0-9]{6}$/,
                                        message: 'Please enter a valid 6-digit PIN code'
                                    }
                                })}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                            />
                            {errors.pincode && (
                                <p className="mt-1 text-sm text-red-600">{errors.pincode.message}</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <div className="flex justify-end">
                <button
                    type="submit"
                    className="bg-primary text-white py-3 px-6 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50"
                >
                    Continue to Payment
                </button>
            </div>
        </form>
    );
};

export default ShippingForm; 