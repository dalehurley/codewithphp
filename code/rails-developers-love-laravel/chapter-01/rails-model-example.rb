# Rails Model Example
# app/models/post.rb

class Post < ApplicationRecord
  # Associations
  belongs_to :user
  has_many :comments, dependent: :destroy
  has_many :tags, through: :post_tags
  has_and_belongs_to_many :categories

  # Validations
  validates :title, presence: true, length: { minimum: 5, maximum: 255 }
  validates :body, presence: true
  validates :user_id, presence: true

  # Scopes
  scope :published, -> { where(published: true) }
  scope :recent, -> { order(created_at: :desc) }
  scope :by_user, ->(user_id) { where(user_id: user_id) }

  # Callbacks
  before_save :generate_slug
  after_create :notify_user

  # Enums
  enum status: { draft: 0, published: 1, archived: 2 }

  # Custom methods
  def publish!
    update!(published: true, published_at: Time.current)
  end

  def draft?
    !published?
  end

  def excerpt
    body.truncate(200)
  end

  private

  def generate_slug
    self.slug = title.parameterize
  end

  def notify_user
    UserMailer.post_created(self).deliver_later
  end
end

# app/models/user.rb
class User < ApplicationRecord
  # Associations
  has_many :posts, dependent: :destroy
  has_many :comments, dependent: :destroy

  # Validations
  validates :email, presence: true, uniqueness: true
  validates :name, presence: true

  # Has secure password (built-in)
  has_secure_password

  # Methods
  def posts_count
    posts.count
  end
end

