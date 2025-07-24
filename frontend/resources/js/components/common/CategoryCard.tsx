import React from 'react';
import { Link } from 'react-router-dom';

interface CategoryCardProps {
    id: number;
    title: string;
    image: string;
    link: string;
    description: string;
}

const CategoryCard: React.FC<CategoryCardProps> = ({
    id,
    title,
    image,
    link,
    description
}) => {
    return (
        <Link to={link} className="group">
            <div className="bg-white rounded-lg shadow-md overflow-hidden transition-transform transform hover:-translate-y-1 hover:shadow-xl">
                <div className="relative h-64">
                    <img
                        src={image}
                        alt={title}
                        className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                        <div className="p-6 text-white">
                            <h3 className="text-xl font-semibold mb-2">
                                {title}
                            </h3>
                            <p className="text-sm opacity-90">
                                {description}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    );
};

export default CategoryCard; 