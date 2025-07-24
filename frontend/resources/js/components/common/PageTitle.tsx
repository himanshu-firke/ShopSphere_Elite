import React from 'react';

interface PageTitleProps {
    title: string;
}

const PageTitle: React.FC<PageTitleProps> = ({ title }) => {
    React.useEffect(() => {
        document.title = `${title} - Kanha Furniture`;
    }, [title]);

    return null;
};

export default PageTitle; 