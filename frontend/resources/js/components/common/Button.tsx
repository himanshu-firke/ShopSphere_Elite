import React from 'react';
import { Link } from 'react-router-dom';
import Loading from './Loading';

interface ButtonProps {
    children: React.ReactNode;
    variant?: 'primary' | 'secondary' | 'outline' | 'text';
    size?: 'small' | 'medium' | 'large';
    fullWidth?: boolean;
    loading?: boolean;
    disabled?: boolean;
    href?: string;
    to?: string;
    type?: 'button' | 'submit' | 'reset';
    onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void;
    className?: string;
}

const Button: React.FC<ButtonProps> = ({
    children,
    variant = 'primary',
    size = 'medium',
    fullWidth = false,
    loading = false,
    disabled = false,
    href,
    to,
    type = 'button',
    onClick,
    className = ''
}) => {
    const baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';

    const sizeClasses = {
        small: 'px-3 py-1.5 text-sm',
        medium: 'px-4 py-2',
        large: 'px-6 py-3 text-lg'
    };

    const variantClasses = {
        primary: 'bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500',
        secondary: 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-800',
        outline: 'border-2 border-blue-500 text-blue-500 hover:bg-blue-50 focus:ring-blue-500',
        text: 'text-blue-500 hover:text-blue-600 hover:bg-blue-50 focus:ring-blue-500'
    };

    const classes = `
        ${baseClasses}
        ${sizeClasses[size]}
        ${variantClasses[variant]}
        ${fullWidth ? 'w-full' : ''}
        ${disabled || loading ? 'opacity-50 cursor-not-allowed' : ''}
        ${className}
    `.trim();

    const content = (
        <>
            {loading && (
                <Loading
                    size="small"
                    color={variant === 'primary' ? 'text-white' : 'text-blue-500'}
                />
            )}
            {!loading && children}
        </>
    );

    if (href) {
        return (
            <a
                href={href}
                className={classes}
                target="_blank"
                rel="noopener noreferrer"
            >
                {content}
            </a>
        );
    }

    if (to) {
        return (
            <Link to={to} className={classes}>
                {content}
            </Link>
        );
    }

    return (
        <button
            type={type}
            className={classes}
            onClick={onClick}
            disabled={disabled || loading}
        >
            {content}
        </button>
    );
};

export default Button; 