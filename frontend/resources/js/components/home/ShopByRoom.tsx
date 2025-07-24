import React from 'react';
import { Link } from 'react-router-dom';

const rooms = [
    {
        id: 1,
        name: 'Living Room',
        icon: '/images/icons/living-room.svg',
        slug: 'living-room'
    },
    {
        id: 2,
        name: 'Dining Room',
        icon: '/images/icons/dining-room.svg',
        slug: 'dining-room'
    },
    {
        id: 3,
        name: 'Bed Room',
        icon: '/images/icons/bed-room.svg',
        slug: 'bed-room'
    },
    {
        id: 4,
        name: 'Study Room',
        icon: '/images/icons/study-room.svg',
        slug: 'study-room'
    },
    {
        id: 5,
        name: 'Kids Furniture',
        icon: '/images/icons/kids-furniture.svg',
        slug: 'kids-furniture'
    },
    {
        id: 6,
        name: 'Kitchen',
        icon: '/images/icons/kitchen.svg',
        slug: 'kitchen'
    }
];

const ShopByRoom: React.FC = () => {
    return (
        <section className="py-8">
            <h2 className="text-xl font-medium text-gray-800 mb-6">Shop By Room</h2>
            <div className="border rounded-lg p-4">
                <div className="grid grid-cols-3 md:grid-cols-6 gap-4">
                    {rooms.map((room) => (
                        <Link
                            key={room.id}
                            to={`/category/${room.slug}`}
                            className="flex flex-col items-center group"
                        >
                            <div className="w-12 h-12 mb-2">
                                <img
                                    src={room.icon}
                                    alt={room.name}
                                    className="w-full h-full object-contain group-hover:scale-110 transition-transform"
                                />
                            </div>
                            <span className="text-sm text-gray-600 text-center group-hover:text-blue-600 transition-colors">
                                {room.name}
                            </span>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default ShopByRoom; 