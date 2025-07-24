import React, { createContext, useContext, useReducer, useEffect } from 'react';
import cartService, { Cart as BackendCart, AddToCartRequest } from '../services/cartService';

// Types
interface CartItem {
    id: number;
    name: string;
    price: number;
    quantity: number;
    image: string;
    maxQuantity: number;
}

interface CartState {
    items: CartItem[];
    total: number;
    itemCount: number;
}

interface CartContextType {
    state: CartState;
    addToCart: (item: CartItem) => void;
    removeFromCart: (itemId: number) => void;
    updateQuantity: (itemId: number, quantity: number) => void;
    clearCart: () => void;
}

// Initial state
const initialState: CartState = {
    items: [],
    total: 0,
    itemCount: 0
};

// Actions
type CartAction =
    | { type: 'ADD_ITEM'; payload: CartItem }
    | { type: 'REMOVE_ITEM'; payload: number }
    | { type: 'UPDATE_QUANTITY'; payload: { id: number; quantity: number } }
    | { type: 'CLEAR_CART' }
    | { type: 'LOAD_CART'; payload: CartState };

// Reducer
const cartReducer = (state: CartState, action: CartAction): CartState => {
    switch (action.type) {
        case 'ADD_ITEM': {
            const existingItemIndex = state.items.findIndex(item => item.id === action.payload.id);
            
            if (existingItemIndex > -1) {
                const updatedItems = [...state.items];
                const existingItem = updatedItems[existingItemIndex];
                const newQuantity = existingItem.quantity + action.payload.quantity;
                
                if (newQuantity <= existingItem.maxQuantity) {
                    updatedItems[existingItemIndex] = {
                        ...existingItem,
                        quantity: newQuantity
                    };
                    
                    return {
                        ...state,
                        items: updatedItems,
                        total: state.total + (action.payload.price * action.payload.quantity),
                        itemCount: state.itemCount + action.payload.quantity
                    };
                }
                return state;
            }
            
            return {
                ...state,
                items: [...state.items, action.payload],
                total: state.total + (action.payload.price * action.payload.quantity),
                itemCount: state.itemCount + action.payload.quantity
            };
        }
        
        case 'REMOVE_ITEM': {
            const itemToRemove = state.items.find(item => item.id === action.payload);
            if (!itemToRemove) return state;
            
            return {
                ...state,
                items: state.items.filter(item => item.id !== action.payload),
                total: state.total - (itemToRemove.price * itemToRemove.quantity),
                itemCount: state.itemCount - itemToRemove.quantity
            };
        }
        
        case 'UPDATE_QUANTITY': {
            const itemIndex = state.items.findIndex(item => item.id === action.payload.id);
            if (itemIndex === -1) return state;
            
            const item = state.items[itemIndex];
            if (action.payload.quantity > item.maxQuantity) return state;
            
            const quantityDiff = action.payload.quantity - item.quantity;
            const updatedItems = [...state.items];
            updatedItems[itemIndex] = {
                ...item,
                quantity: action.payload.quantity
            };
            
            return {
                ...state,
                items: updatedItems,
                total: state.total + (item.price * quantityDiff),
                itemCount: state.itemCount + quantityDiff
            };
        }
        
        case 'CLEAR_CART':
            return initialState;
            
        case 'LOAD_CART':
            return action.payload;
            
        default:
            return state;
    }
};

// Context
const CartContext = createContext<CartContextType | undefined>(undefined);

// Provider
export const CartProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [state, dispatch] = useReducer(cartReducer, initialState);

    useEffect(() => {
        // Load cart from localStorage
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            dispatch({ type: 'LOAD_CART', payload: JSON.parse(savedCart) });
        }
    }, []);

    useEffect(() => {
        // Save cart to localStorage whenever it changes
        localStorage.setItem('cart', JSON.stringify(state));
    }, [state]);

    const addToCart = async (item: CartItem) => {
        try {
            const cartRequest: AddToCartRequest = {
                product_id: item.id,
                quantity: item.quantity
            };
            const updatedCart = await cartService.addToCart(cartRequest);
            // Update local state with backend response
            dispatch({ 
                type: 'LOAD_CART', 
                payload: {
                    items: updatedCart.items.map(cartItem => ({
                        id: cartItem.product.id,
                        name: cartItem.product.name,
                        price: cartItem.price,
                        quantity: cartItem.quantity,
                        image: cartItem.product.primary_image?.url || '',
                        maxQuantity: 10 // Default, should come from product stock
                    })),
                    total: updatedCart.total,
                    itemCount: updatedCart.item_count
                }
            });
        } catch (error) {
            console.error('Failed to add item to cart:', error);
            // Fallback to local storage for offline functionality
            dispatch({ type: 'ADD_ITEM', payload: item });
        }
    };

    const removeFromCart = (itemId: number) => {
        dispatch({ type: 'REMOVE_ITEM', payload: itemId });
    };

    const updateQuantity = (itemId: number, quantity: number) => {
        dispatch({ type: 'UPDATE_QUANTITY', payload: { id: itemId, quantity } });
    };

    const clearCart = () => {
        dispatch({ type: 'CLEAR_CART' });
    };

    return (
        <CartContext.Provider
            value={{
                state,
                addToCart,
                removeFromCart,
                updateQuantity,
                clearCart
            }}
        >
            {children}
        </CartContext.Provider>
    );
};

// Hook
export const useCart = () => {
    const context = useContext(CartContext);
    if (context === undefined) {
        throw new Error('useCart must be used within a CartProvider');
    }
    return context;
};

export default CartContext; 