# Rails Routing Example
# config/routes.rb

Rails.application.routes.draw do
  # Root route
  root 'pages#home'

  # RESTful resource
  resources :posts do
    resources :comments
  end

  # Specific routes
  get 'about', to: 'pages#about'
  post 'contact', to: 'pages#contact'

  # Namespace for admin
  namespace :admin do
    resources :users
    resources :posts
  end

  # API namespace with versioning
  namespace :api do
    namespace :v1 do
      resources :posts, only: [:index, :show]
      resources :users, only: [:index, :show]
    end
  end

  # Redirect
  get 'old-posts', to: redirect('/posts')

  # Catch-all (must be last)
  match '*path', to: 'pages#not_found', via: :all
end





