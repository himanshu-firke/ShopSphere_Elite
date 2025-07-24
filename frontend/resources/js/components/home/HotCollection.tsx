import React from 'react';
import { Link } from 'react-router-dom';

const collections = [
    {
        id: 1,
        name: 'Milford Collection',
        image: '/images/collections/milford.jpg',
        slug: 'milford'
    },
    {
        id: 2,
        name: 'Mozart Collection',
        image: '/images/collections/mozart.jpg',
        slug: 'mozart'
    },
    {
        id: 3,
        name: 'Maxsif Collection',
        image: '/images/collections/maxsif.jpg',
        slug: 'maxsif'
    }
];

const HotCollection: React.FC = () => {
    return (
        <section className="py-8">
            <h2 className="text-xl font-medium text-gray-800 mb-6">Hot Collection</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {collections.map((collection) => (
                    <Link
                        key={collection.id}
                        to={`/collection/${collection.slug}`}
                        className="group relative overflow-hidden rounded-lg shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div className="aspect-w-16 aspect-h-9">
                            <img
                                src={collection.image}
                                alt={collection.name}
                                className="w-full h-full object-cover"
                            />
                        </div>
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="bg-white/90 px-6 py-2 rounded-full text-gray-800 font-medium transform group-hover:scale-110 transition-transform">
                                {collection.name}
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
};

export default HotCollection; 