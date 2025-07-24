import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { FiChevronLeft, FiChevronRight } from 'react-icons/fi';

interface Slide {
    id: number;
    image: string;
    title: string;
    description: string;
    buttonText: string;
    link: string;
}

const slides: Slide[] = [
    {
        id: 1,
        image: '/images/banners/bedroom.jpg',
        title: 'Modern Bedroom Collection',
        description: 'Transform your bedroom into a luxurious retreat with our modern furniture collection',
        buttonText: 'Shop Now',
        link: '/category/bedroom'
    },
    {
        id: 2,
        image: '/images/banners/dining-set.jpg',
        title: 'Elegant Dining Sets',
        description: 'Create memorable dining experiences with our elegant dining furniture',
        buttonText: 'Explore More',
        link: '/category/dining'
    },
    {
        id: 3,
        image: '/images/banners/office.jpg',
        title: 'Work From Home Essentials',
        description: 'Set up your perfect home office with our ergonomic furniture',
        buttonText: 'View Collection',
        link: '/category/office'
    }
];

const HeroSlider: React.FC = () => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [isAutoPlaying, setIsAutoPlaying] = useState(true);

    useEffect(() => {
        let interval: NodeJS.Timeout;
        
        if (isAutoPlaying) {
            interval = setInterval(() => {
                setCurrentSlide((prev) => (prev + 1) % slides.length);
            }, 5000); // Change slide every 5 seconds
        }

        return () => {
            if (interval) {
                clearInterval(interval);
            }
        };
    }, [isAutoPlaying]);

    const goToSlide = (index: number) => {
        setCurrentSlide(index);
        setIsAutoPlaying(false);
    };

    const goToPrevSlide = () => {
        setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);
        setIsAutoPlaying(false);
    };

    const goToNextSlide = () => {
        setCurrentSlide((prev) => (prev + 1) % slides.length);
        setIsAutoPlaying(false);
    };

    return (
        <div className="relative h-[500px] overflow-hidden group">
            {/* Slides */}
            <div className="relative h-full">
                {slides.map((slide, index) => (
                    <div
                        key={slide.id}
                        className={`absolute inset-0 transition-opacity duration-500 ${
                            index === currentSlide ? 'opacity-100' : 'opacity-0'
                        }`}
                    >
                        {/* Background Image */}
                        <div
                            className="absolute inset-0 bg-cover bg-center"
                            style={{ backgroundImage: `url(${slide.image})` }}
                        >
                            <div className="absolute inset-0 bg-black bg-opacity-40" />
                        </div>

                        {/* Content */}
                        <div className="relative h-full flex items-center">
                            <div className="container mx-auto px-4">
                                <div className="max-w-2xl text-white">
                                    <h2 className="text-4xl md:text-5xl font-bold mb-4">
                                        {slide.title}
                                    </h2>
                                    <p className="text-lg md:text-xl mb-8">
                                        {slide.description}
                                    </p>
                                    <Link
                                        to={slide.link}
                                        className="inline-block bg-white text-primary px-8 py-3 rounded-full font-semibold hover:bg-primary hover:text-white transition-colors"
                                    >
                                        {slide.buttonText}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Navigation Arrows */}
            <button
                onClick={goToPrevSlide}
                className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 p-2 rounded-full text-gray-800 hover:bg-opacity-100 opacity-0 group-hover:opacity-100 transition-opacity"
                aria-label="Previous slide"
            >
                <FiChevronLeft className="w-6 h-6" />
            </button>
            <button
                onClick={goToNextSlide}
                className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 p-2 rounded-full text-gray-800 hover:bg-opacity-100 opacity-0 group-hover:opacity-100 transition-opacity"
                aria-label="Next slide"
            >
                <FiChevronRight className="w-6 h-6" />
            </button>

            {/* Dots Navigation */}
            <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                {slides.map((_, index) => (
                    <button
                        key={index}
                        onClick={() => goToSlide(index)}
                        className={`w-3 h-3 rounded-full transition-all ${
                            index === currentSlide
                                ? 'bg-white scale-110'
                                : 'bg-white bg-opacity-50 hover:bg-opacity-75'
                        }`}
                        aria-label={`Go to slide ${index + 1}`}
                    />
                ))}
            </div>
        </div>
    );
};

export default HeroSlider; 