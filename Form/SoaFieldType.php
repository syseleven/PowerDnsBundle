<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
namespace SysEleven\PowerDnsBundle\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SysEleven\PowerDnsBundle\Form\Transformer\SoaTransformer;
use Symfony\Component\Form\AbstractType;


/**
 * Class SoaFieldType
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
class SoaFieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('primary', 'text', array('required' => false))
            ->add('hostmaster', 'text', array('required' => false))
            ->add('serial','text',array('required' => false))
            ->add('refresh','text',array('required' => false))
            ->add('expire','text',array('required' => false))
            ->add('default_ttl','text', array('required' => false));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'soa';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'SysEleven\PowerDnsBundle\Lib\Soa',
            ));
    }
}
 