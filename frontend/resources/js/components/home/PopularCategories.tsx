import React from 'react';
import { Link } from 'react-router-dom';

const categories = [
    {
        id: 1,
        name: 'Plastic Chairs',
        image: '/images/categories/plastic-chairs.jpg',
        slug: 'plastic-chairs',
        discount: 'UPTO 50% OFF'
    },
    {
        id: 2,
        name: 'Office Chairs',
        image: '/images/categories/office-chairs.jpg',
        slug: 'office-chairs',
        discount: 'UPTO 45% OFF'
    },
    {
        id: 3,
        name: 'Recliners',
        image: '/images/categories/recliners.jpg',
        slug: 'recliners',
        discount: 'UPTO 65% OFF'
    }
];

const PopularCategories: React.FC = () => {
    return (
        <section className="py-8">
            <h2 className="text-xl font-medium text-gray-800 mb-6">Popular Categories</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {categories.map((category) => (
                    <Link
                        key={category.id}
                        to={`/category/${category.slug}`}
                        className="group relative overflow-hidden rounded-lg"
                    >
                        <div className="aspect-w-4 aspect-h-3">
                            <img
                                src={category.image}
                                alt={category.name}
                                className="w-full h-full object-cover"
                            />
                        </div>
                        <div className="absolute top-4 left-4 bg-red-600 text-white text-sm font-medium px-3 py-1 rounded">
                            {category.discount}
                        </div>
                        <div className="absolute bottom-4 left-4 right-4">
                            <div className="bg-white text-center py-2 rounded">
                                <span className="text-gray-800 font-medium">Shop Now</span>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
};

export default PopularCategories; 