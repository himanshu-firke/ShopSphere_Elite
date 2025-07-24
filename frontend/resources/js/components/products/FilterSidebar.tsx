import React, { useState } from 'react';
import { FiChevronDown, FiChevronUp } from 'react-icons/fi';

interface FilterSection {
    id: string;
    title: string;
    type: 'checkbox' | 'radio' | 'range';
    options?: { id: string; label: string; count: number }[];
    range?: { min: number; max: number; step: number };
}

interface FilterSidebarProps {
    sections: FilterSection[];
    selectedFilters: Record<string, string[] | number[]>;
    onFilterChange: (sectionId: string, value: string[] | number[]) => void;
}

const FilterSidebar: React.FC<FilterSidebarProps> = ({
    sections,
    selectedFilters,
    onFilterChange
}) => {
    const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>(
        sections.reduce((acc, section) => ({ ...acc, [section.id]: true }), {})
    );

    const toggleSection = (sectionId: string) => {
        setExpandedSections(prev => ({
            ...prev,
            [sectionId]: !prev[sectionId]
        }));
    };

    const handleCheckboxChange = (sectionId: string, value: string) => {
        const currentValues = (selectedFilters[sectionId] || []) as string[];
        const newValues = currentValues.includes(value)
            ? currentValues.filter(v => v !== value)
            : [...currentValues, value];
        onFilterChange(sectionId, newValues);
    };

    const handleRadioChange = (sectionId: string, value: string) => {
        onFilterChange(sectionId, [value]);
    };

    const handleRangeChange = (sectionId: string, value: number) => {
        onFilterChange(sectionId, [value]);
    };

    return (
        <div className="w-64 bg-white shadow-sm rounded-lg p-4">
            <h2 className="text-lg font-bold text-gray-900 mb-4">Filters</h2>

            <div className="space-y-4">
                {sections.map(section => (
                    <div key={section.id} className="border-b border-gray-200 pb-4 last:border-0">
                        <button
                            className="w-full flex items-center justify-between text-left"
                            onClick={() => toggleSection(section.id)}
                        >
                            <span className="text-sm font-medium text-gray-900">
                                {section.title}
                            </span>
                            {expandedSections[section.id] ? (
                                <FiChevronUp className="w-4 h-4 text-gray-500" />
                            ) : (
                                <FiChevronDown className="w-4 h-4 text-gray-500" />
                            )}
                        </button>

                        {expandedSections[section.id] && (
                            <div className="mt-2 space-y-2">
                                {section.type === 'checkbox' && section.options && (
                                    <div className="space-y-2">
                                        {section.options.map(option => (
                                            <label
                                                key={option.id}
                                                className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-900"
                                            >
                                                <input
                                                    type="checkbox"
                                                    className="rounded border-gray-300 text-primary focus:ring-primary"
                                                    checked={(selectedFilters[section.id] || []).includes(option.id)}
                                                    onChange={() => handleCheckboxChange(section.id, option.id)}
                                                />
                                                <span>{option.label}</span>
                                                <span className="text-gray-400 text-xs">({option.count})</span>
                                            </label>
                                        ))}
                                    </div>
                                )}

                                {section.type === 'radio' && section.options && (
                                    <div className="space-y-2">
                                        {section.options.map(option => (
                                            <label
                                                key={option.id}
                                                className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-900"
                                            >
                                                <input
                                                    type="radio"
                                                    className="border-gray-300 text-primary focus:ring-primary"
                                                    name={section.id}
                                                    value={option.id}
                                                    checked={(selectedFilters[section.id] || [])[0] === option.id}
                                                    onChange={() => handleRadioChange(section.id, option.id)}
                                                />
                                                <span>{option.label}</span>
                                                <span className="text-gray-400 text-xs">({option.count})</span>
                                            </label>
                                        ))}
                                    </div>
                                )}

                                {section.type === 'range' && section.range && (
                                    <div className="space-y-2">
                                        <input
                                            type="range"
                                            min={section.range.min}
                                            max={section.range.max}
                                            step={section.range.step}
                                            value={(selectedFilters[section.id] || [])[0] || section.range.min}
                                            onChange={(e) => handleRangeChange(section.id, Number(e.target.value))}
                                            className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                        />
                                        <div className="flex justify-between text-xs text-gray-500">
                                            <span>₹{section.range.min}</span>
                                            <span>₹{section.range.max}</span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default FilterSidebar; 