<?php

namespace app\modules\service\models;

use app\modules\admin\behaviors\SlugBehavior;
use app\modules\admin\traits\QueryExceptions;
use app\modules\seo\behaviors\SeoBehavior;
use app\modules\seo\valueObjects\Seo;
use DomainException;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii2tech\ar\position\PositionBehavior;

/**
 * @mixin SeoBehavior
 * @mixin SlugBehavior
 * @mixin PositionBehavior
 *
 * @property ServiceImage $mainImage
 * @property ServiceImage[] $images
 *
 * @property int $id [int(11)]
 * @property int $image_id [int(11)]
 * @property int $position [int(11)]
 * @property int $status [int(11)]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 * @property int $price_type [int(11)]
 * @property string $price [decimal(10)]
 * @property string $title [varchar(255)]
 * @property string $alias [varchar(255)]
 * @property string $description
 * @property string $meta_t [varchar(255)]
 * @property string $meta_d [varchar(255)]
 * @property string $meta_k [varchar(255)]
 * @property string $h1 [varchar(255)]
 */

class Service extends ActiveRecord
{
    use QueryExceptions;

    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;

    const TYPE_STATIC = 0;
    const TYPE_RANGE = 1;

    /**
     * @var Seo
     */
    public $seo;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            SlugBehavior::class,
            SeoBehavior::class,
            PositionBehavior::class,
            [
                'class' => SaveRelationsBehavior::class,
                'relations' => ['images'],
            ],
        ];
    }

    public static function tableName()
    {
        return 'services';
    }

    public static function create($title, $price_type, $price, $description, ?Seo $seo = null): self
    {
        $self = new self();

        $self->price_type = $price_type;
        $self->price = $price;
        $self->status = self::STATUS_ACTIVE;
        $self->title = $title;
        $self->description = $description;

        $self->seo = $seo ?? Seo::blank();

        return $self;
    }

    public function edit($price_type, $price, $title, $description, ?Seo $seo = null): void
    {
        $this->price_type = $price_type;
        $this->price = $price;
        $this->title = $title;
        $this->description = $description;
        $this->seo = $seo ?? Seo::blank();
    }

    public function beforeSave($insert)
    {
        $this->setAttribute('meta_t', $this->seo->getTitle());
        $this->setAttribute('meta_d', $this->seo->getDescription());
        $this->setAttribute('meta_k', $this->seo->getKeywords());
        $this->setAttribute('h1', $this->seo->getH1());

        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        $this->seo = new Seo(
            $this->getAttribute('meta_t'),
            $this->getAttribute('meta_d'),
            $this->getAttribute('meta_k'),
            $this->getAttribute('h1')
        );
        parent::afterFind();
    }

    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function getMainImage()
    {
        return $this->hasOne(ServiceImage::class, ['id' => 'image_id']);
    }

    public function getImages()
    {
        return $this->hasMany(ServiceImage::class, ['service_id' => 'id'])->orderBy('position');
    }

    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('Товар уже активный');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    public function deactivate(): void
    {
        if (!$this->isActive()) {
            throw new DomainException('Товар уже неактивный');
        }
        $this->status = self::STATUS_DRAFT;
    }

    public function addImage(ServiceImage $image)
    {
        $images = $this->images;
        $images[] = $image;
        $this->updateImages($images);
    }

    private function updateImages(array $images)
    {
        foreach ($images as $i => $image) {
            $image->setPosition($i + 1);
        }
        $this->images = $images;
        $this->populateRelation('mainImage', reset($images));
    }

    public function sortImages(int $oldIndex, int $newIndex)
    {
        $images = $this->images;
        $tmp = $images[$oldIndex];
        array_splice($images, $oldIndex, 1);
        array_splice($images, $newIndex, 0, [$tmp]);
        $this->updateImages($images);
    }

    public function deleteImage($photoId)
    {
        $images = $this->images;
        foreach ($images as $i => $image) {
            if ($image->id == $photoId) {
                unset($images[$i]);
                $this->updateImages($images);
                return;
            }
        }
        throw new DomainException('Картинка не найдена');
    }

    public function getFirstImage()
    {
        if (!$this->images) {
            return '';
        }
        $images = $this->images;
        return Url::to($images[0]->getImageFileUrl('image'), true);
    }
}