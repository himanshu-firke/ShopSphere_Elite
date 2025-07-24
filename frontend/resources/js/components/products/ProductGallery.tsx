import React, { useState } from 'react';
import { FiChevronLeft, FiChevronRight } from 'react-icons/fi';

interface ProductImage {
    id: number;
    url: string;
    alt: string;
}

interface ProductGalleryProps {
    images: ProductImage[];
}

const ProductGallery: React.FC<ProductGalleryProps> = ({ images }) => {
    const [currentImage, setCurrentImage] = useState(0);
    const [isZoomed, setIsZoomed] = useState(false);
    const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });

    const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
        if (!isZoomed) return;

        const { left, top, width, height } = e.currentTarget.getBoundingClientRect();
        const x = ((e.clientX - left) / width) * 100;
        const y = ((e.clientY - top) / height) * 100;

        setMousePosition({ x, y });
    };

    const nextImage = () => {
        setCurrentImage((prev) => (prev + 1) % images.length);
    };

    const previousImage = () => {
        setCurrentImage((prev) => (prev - 1 + images.length) % images.length);
    };

    return (
        <div className="w-full">
            {/* Main Image */}
            <div className="relative aspect-square overflow-hidden rounded-lg mb-4 bg-gray-100">
                <div
                    className={`relative w-full h-full cursor-zoom-in ${
                        isZoomed ? 'cursor-zoom-out' : 'cursor-zoom-in'
                    }`}
                    onClick={() => setIsZoomed(!isZoomed)}
                    onMouseMove={handleMouseMove}
                    onMouseLeave={() => setIsZoomed(false)}
                >
                    <img
                        src={images[currentImage].url}
                        alt={images[currentImage].alt}
                        className={`w-full h-full object-cover transition-transform duration-200 ${
                            isZoomed ? 'scale-150' : ''
                        }`}
                        style={
                            isZoomed
                                ? {
                                      transformOrigin: `${mousePosition.x}% ${mousePosition.y}%`
                                  }
                                : undefined
                        }
                    />
                </div>

                {/* Navigation Arrows */}
                {images.length > 1 && (
                    <>
                        <button
                            onClick={previousImage}
                            className="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-md transition-colors"
                        >
                            <FiChevronLeft className="w-5 h-5" />
                        </button>
                        <button
                            onClick={nextImage}
                            className="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-md transition-colors"
                        >
                            <FiChevronRight className="w-5 h-5" />
                        </button>
                    </>
                )}
            </div>

            {/* Thumbnails */}
            {images.length > 1 && (
                <div className="grid grid-cols-5 gap-2">
                    {images.map((image, index) => (
                        <button
                            key={image.id}
                            onClick={() => setCurrentImage(index)}
                            className={`aspect-square rounded-md overflow-hidden border-2 transition-colors ${
                                currentImage === index
                                    ? 'border-primary'
                                    : 'border-transparent hover:border-gray-300'
                            }`}
                        >
                            <img
                                src={image.url}
                                alt={image.alt}
                                className="w-full h-full object-cover"
                            />
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
};

export default ProductGallery; 