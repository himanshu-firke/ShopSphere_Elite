import api from './api';

export interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    sale_price?: number;
    sku: string;
    stock_quantity: number;
    category_id: number;
    category?: {
        id: number;
        name: string;
        slug: string;
    };
    images: Array<{
        id: number;
        url: string;
        alt_text?: string;
        is_primary: boolean;
    }>;
    attributes?: Array<{
        id: number;
        name: string;
        value: string;
    }>;
    reviews?: Array<{
        id: number;
        rating: number;
        comment: string;
        user_name: string;
        created_at: string;
    }>;
    average_rating?: number;
    reviews_count?: number;
    is_featured: boolean;
    status: string;
    created_at: string;
    updated_at: string;
}

export interface ProductFilters {
    category_id?: number;
    search?: string;
    min_price?: number;
    max_price?: number;
    sort_by?: 'name' | 'price' | 'created_at' | 'rating';
    sort_order?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface ProductResponse {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

class ProductService {
    // Get all products with filters and pagination
    async getProducts(filters: ProductFilters = {}): Promise<ProductResponse> {
        const response = await api.get('/products', { params: filters });
        return response.data;
    }

    // Get single product by ID
    async getProduct(id: number): Promise<Product> {
        const response = await api.get(`/products/${id}`);
        return response.data.data;
    }

    // Get featured products
    async getFeaturedProducts(): Promise<Product[]> {
        const response = await api.get('/products/featured');
        return response.data.data;
    }

    // Get bestseller products
    async getBestsellerProducts(): Promise<Product[]> {
        const response = await api.get('/products/bestsellers');
        return response.data.data;
    }

    // Get related products
    async getRelatedProducts(productId: number): Promise<Product[]> {
        const response = await api.get(`/products/${productId}/related`);
        return response.data.data;
    }

    // Search products
    async searchProducts(query: string, filters: ProductFilters = {}): Promise<ProductResponse> {
        const response = await api.get('/products/search', { 
            params: { search: query, ...filters } 
        });
        return response.data;
    }
}

export default new ProductService();
