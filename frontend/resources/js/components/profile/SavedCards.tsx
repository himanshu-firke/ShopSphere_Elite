import React, { useState } from 'react';
import { FiCreditCard, FiTrash2, FiPlus } from 'react-icons/fi';

// Mock saved cards data - replace with API call
const mockCards = [
    {
        id: 1,
        cardNumber: '•••• •••• •••• 4242',
        expiryDate: '12/25',
        cardHolder: 'John Doe',
        type: 'visa',
        isDefault: true
    },
    {
        id: 2,
        cardNumber: '•••• •••• •••• 5555',
        expiryDate: '09/24',
        cardHolder: 'John Doe',
        type: 'mastercard',
        isDefault: false
    }
];

interface Card {
    id: number;
    cardNumber: string;
    expiryDate: string;
    cardHolder: string;
    type: 'visa' | 'mastercard';
    isDefault: boolean;
}

const SavedCards: React.FC = () => {
    const [cards, setCards] = useState<Card[]>(mockCards);
    const [isAddCardModalOpen, setIsAddCardModalOpen] = useState(false);
    const [newCard, setNewCard] = useState({
        cardNumber: '',
        expiryDate: '',
        cardHolder: '',
        cvv: ''
    });

    const handleSetDefault = (cardId: number) => {
        setCards(cards.map(card => ({
            ...card,
            isDefault: card.id === cardId
        })));
    };

    const handleDeleteCard = (cardId: number) => {
        if (confirm('Are you sure you want to delete this card?')) {
            setCards(cards.filter(card => card.id !== cardId));
        }
    };

    const handleAddCard = (e: React.FormEvent) => {
        e.preventDefault();
        // Implement card addition logic
        console.log('Adding new card:', newCard);
        setIsAddCardModalOpen(false);
        setNewCard({
            cardNumber: '',
            expiryDate: '',
            cardHolder: '',
            cvv: ''
        });
    };

    const formatCardNumber = (value: string) => {
        const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        const matches = v.match(/\d{4,16}/g);
        const match = (matches && matches[0]) || '';
        const parts = [];

        for (let i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4));
        }

        if (parts.length) {
            return parts.join(' ');
        }
        return value;
    };

    const getCardIcon = (type: string) => {
        switch (type) {
            case 'visa':
                return <img src="/images/payment/visa.png" alt="Visa" className="h-8" />;
            case 'mastercard':
                return <img src="/images/payment/mastercard.png" alt="Mastercard" className="h-8" />;
            default:
                return <FiCreditCard className="w-8 h-8" />;
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="text-xl font-bold text-gray-900">Saved Cards</h2>
                <button
                    onClick={() => setIsAddCardModalOpen(true)}
                    className="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
                >
                    <FiPlus className="w-4 h-4" />
                    <span>Add New Card</span>
                </button>
            </div>

            {/* Cards Grid */}
            <div className="grid gap-6 md:grid-cols-2">
                {cards.map(card => (
                    <div
                        key={card.id}
                        className={`relative p-6 rounded-lg border ${
                            card.isDefault ? 'border-primary bg-primary/5' : 'border-gray-200'
                        }`}
                    >
                        <div className="flex justify-between items-start mb-4">
                            {getCardIcon(card.type)}
                            <button
                                onClick={() => handleDeleteCard(card.id)}
                                className="text-gray-400 hover:text-red-500"
                            >
                                <FiTrash2 className="w-5 h-5" />
                            </button>
                        </div>

                        <p className="text-lg font-medium text-gray-900 mb-1">
                            {card.cardNumber}
                        </p>
                        <p className="text-sm text-gray-500 mb-4">
                            Expires {card.expiryDate}
                        </p>
                        <p className="text-sm text-gray-700">
                            {card.cardHolder}
                        </p>

                        {!card.isDefault && (
                            <button
                                onClick={() => handleSetDefault(card.id)}
                                className="mt-4 text-sm text-primary hover:text-primary-dark font-medium"
                            >
                                Set as Default
                            </button>
                        )}
                        {card.isDefault && (
                            <span className="mt-4 block text-sm text-primary font-medium">
                                Default Card
                            </span>
                        )}
                    </div>
                ))}
            </div>

            {/* Add Card Modal */}
            {isAddCardModalOpen && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg max-w-lg w-full">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-xl font-bold text-gray-900">
                                    Add New Card
                                </h2>
                                <button
                                    onClick={() => setIsAddCardModalOpen(false)}
                                    className="text-gray-400 hover:text-gray-500"
                                >
                                    <span className="sr-only">Close</span>
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form onSubmit={handleAddCard}>
                                <div className="space-y-4">
                                    <div>
                                        <label
                                            htmlFor="cardNumber"
                                            className="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Card Number
                                        </label>
                                        <input
                                            type="text"
                                            id="cardNumber"
                                            value={newCard.cardNumber}
                                            onChange={(e) => setNewCard({
                                                ...newCard,
                                                cardNumber: formatCardNumber(e.target.value)
                                            })}
                                            maxLength={19}
                                            placeholder="1234 5678 9012 3456"
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                htmlFor="expiryDate"
                                                className="block text-sm font-medium text-gray-700 mb-1"
                                            >
                                                Expiry Date
                                            </label>
                                            <input
                                                type="text"
                                                id="expiryDate"
                                                value={newCard.expiryDate}
                                                onChange={(e) => {
                                                    let value = e.target.value.replace(/\D/g, '');
                                                    if (value.length >= 2) {
                                                        value = value.slice(0, 2) + '/' + value.slice(2, 4);
                                                    }
                                                    setNewCard({
                                                        ...newCard,
                                                        expiryDate: value
                                                    });
                                                }}
                                                maxLength={5}
                                                placeholder="MM/YY"
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                            />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="cvv"
                                                className="block text-sm font-medium text-gray-700 mb-1"
                                            >
                                                CVV
                                            </label>
                                            <input
                                                type="text"
                                                id="cvv"
                                                value={newCard.cvv}
                                                onChange={(e) => setNewCard({
                                                    ...newCard,
                                                    cvv: e.target.value.replace(/\D/g, '')
                                                })}
                                                maxLength={4}
                                                placeholder="123"
                                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="cardHolder"
                                            className="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Card Holder Name
                                        </label>
                                        <input
                                            type="text"
                                            id="cardHolder"
                                            value={newCard.cardHolder}
                                            onChange={(e) => setNewCard({
                                                ...newCard,
                                                cardHolder: e.target.value.toUpperCase()
                                            })}
                                            placeholder="JOHN DOE"
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                        />
                                    </div>
                                </div>

                                <div className="mt-6 flex justify-end gap-4">
                                    <button
                                        type="button"
                                        onClick={() => setIsAddCardModalOpen(false)}
                                        className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
                                    >
                                        Add Card
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default SavedCards; 