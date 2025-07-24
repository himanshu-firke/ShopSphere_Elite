import React from 'react';
import { Link } from 'react-router-dom';

interface Category {
    id: number;
    name: string;
    slug: string;
    image: string;
}

const categories: Category[] = [
    {
        id: 1,
        name: 'Living Room',
        slug: 'living-room',
        image: '/images/categories/living-room.jpg'
    },
    {
        id: 2,
        name: 'Bedroom',
        slug: 'bedroom',
        image: '/images/categories/bedroom.jpg'
    },
    {
        id: 3,
        name: 'Dining Room',
        slug: 'dining-room',
        image: '/images/categories/dining-room.jpg'
    },
    {
        id: 4,
        name: 'Home Office',
        slug: 'home-office',
        image: '/images/categories/home-office.jpg'
    },
    {
        id: 5,
        name: 'Kitchen',
        slug: 'kitchen',
        image: '/images/categories/kitchen.jpg'
    },
    {
        id: 6,
        name: 'Outdoor',
        slug: 'outdoor',
        image: '/images/categories/outdoor.jpg'
    }
];

const FeaturedCategories: React.FC = () => {
    return (
        <section className="py-12">
            <div className="text-center mb-8">
                <h2 className="text-3xl font-bold text-gray-900">Shop By Category</h2>
                <p className="text-gray-600 mt-2">
                    Explore our wide range of furniture categories
                </p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                {categories.map((category) => (
                    <Link
                        key={category.id}
                        to={`/category/${category.slug}`}
                        className="group relative overflow-hidden rounded-lg"
                    >
                        <div className="aspect-w-1 aspect-h-1 w-full">
                            <img
                                src={category.image}
                                alt={category.name}
                                className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent">
                                <div className="absolute bottom-4 left-0 right-0 text-center">
                                    <h3 className="text-white text-lg font-semibold px-2">
                                        {category.name}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
};

export default FeaturedCategories; 