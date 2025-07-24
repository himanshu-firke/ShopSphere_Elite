import React from 'react';
import { Helmet } from 'react-helmet-async';
import HeroSlider from '../components/home/HeroSlider';
import ShopByRoom from '../components/home/ShopByRoom';
import HotCollection from '../components/home/HotCollection';
import PopularCategories from '../components/home/PopularCategories';
import FeaturedProducts from '../components/home/FeaturedProducts';
import BenefitsSection from '../components/home/BenefitsSection';
import NewsletterSection from '../components/home/NewsletterSection';

const Home: React.FC = () => {
    return (
        <>
            <Helmet>
                <title>Kanha Furniture - Modern Furniture Store</title>
                <meta name="description" content="Shop the best collection of modern furniture for your home. Find sofas, beds, dining sets, and more at great prices." />
            </Helmet>

            <main>
                <HeroSlider />
                <div className="container mx-auto px-4">
                    <ShopByRoom />
                    <HotCollection />
                    <PopularCategories />
                    <FeaturedProducts />
                </div>
                <BenefitsSection />
                <NewsletterSection />
            </main>
        </>
    );
};

export default Home; 